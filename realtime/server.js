/**
 * HivePHP realtime server (Socket.IO + Express push endpoint).
 *
 * Responsibilities
 *   1. Authenticate browser sockets with a short-lived HMAC-signed token that
 *      the PHP backend hands out (never trusted raw user input).
 *   2. Put each authenticated socket into a per-user room so PHP can target a
 *      single recipient.
 *   3. Expose an HMAC-protected HTTP POST /emit endpoint that PHP calls to push
 *      an event to one (or more) users in real time.
 *
 * Security model
 *   - The shared secret lives only on the PHP side (env REALTIME_SECRET) and
 *     here (env REALTIME_SECRET). Both are configured by the operator.
 *   - Socket tokens are HMAC-SHA256(secret, payload). They cannot be forged
 *     without the secret and expire after REALTIME_TOKEN_TTL seconds.
 *   - /emit requests carry a timestamp and an HMAC signature. The timestamp is
 *     checked against a skew window, and the signature is compared in
 *     constant time to prevent timing attacks.
 *   - The Node server never reads the database; every message originates from
 *     authorized PHP code.
 *
 * Run:  npm install && npm run install:client && npm start
 */

const http = require('http');
const fs   = require('fs');
const path = require('path');
const crypto = require('crypto');
const express = require('express');
const { Server } = require('socket.io');

/* ------------------------------------------------------------------ *
 * Load .env from project root (mirrors PHP's dotenv)                  *
 * ------------------------------------------------------------------ */
(function loadDotenv() {
  try {
    const envPath = path.join(__dirname, '..', '.env');
    const lines = fs.readFileSync(envPath, 'utf8').split('\n');
    for (const raw of lines) {
      const line = raw.replace(/\r$/, '').trim();
      if (!line || line.startsWith('#')) continue;
      const eq = line.indexOf('=');
      if (eq < 1) continue;
      const key = line.slice(0, eq).trim();
      const val = line.slice(eq + 1).trim();
      if (!(key in process.env)) {
        process.env[key] = val;
      }
    }
  } catch { /* .env not found — rely on process.env */ }
})();

/* ------------------------------------------------------------------ *
 * Configuration (mirrors config/realtime.php on the PHP side)         *
 * ------------------------------------------------------------------ */

const PORT = parseInt(process.env.REALTIME_PORT || '3001', 10);
const PATH = process.env.REALTIME_PATH || '/socket.io';
const SECRET = process.env.REALTIME_SECRET || 'change-me-put-a-long-random-string-here';
const TOKEN_TTL = parseInt(process.env.REALTIME_TOKEN_TTL || '300', 10);
// Maximum accepted clock skew for /emit timestamps (seconds).
const EMIT_SKEW = 30;
// Simple per-IP throttling for /emit (requests / window).
const EMIT_RATE = { windowMs: 60_000, max: 300 };
// Simple per-IP throttling for connection attempts.
const CONNECT_RATE = { windowMs: 60_000, max: 100 };
// CORS origins allowed to connect sockets. '*' is permissive; set an explicit
// list (e.g. http://hivephp.local) for production.
const CORS_ORIGINS = process.env.REALTIME_CORS || '*';

if (SECRET === 'change-me-put-a-long-random-string-here') {
  console.warn(
    '[realtime] WARNING: REALTIME_SECRET is still the default value. ' +
    'Set a long random secret in .env (PHP) and the environment (Node).'
  );
}

/* ------------------------------------------------------------------ *
 * Helpers                                                             *
 * ------------------------------------------------------------------ */

/** Constant-time string comparison. */
function safeEqual(a, b) {
  const bufA = Buffer.from(String(a));
  const bufB = Buffer.from(String(b));
  return bufA.length === bufB.length && crypto.timingSafeEqual(bufA, bufB);
}

function hmac(data) {
  return crypto.createHmac('sha256', SECRET).update(String(data)).digest('base64url');
}

function hmacVerify(expected, data) {
  try {
    return safeEqual(expected, hmac(data));
  } catch {
    return false;
  }
}

function nowSec() {
  return Math.floor(Date.now() / 1000);
}

/**
 * Parse and validate a socket token: "<base64url(payload)>.<base64url(sig)>".
 * Returns { uid, exp } or null on any failure.
 */
function verifyToken(token) {
  if (typeof token !== 'string' || !token.includes('.')) return null;

  const [payloadPart, sig] = token.split('.');
  if (!payloadPart || !sig) return null;

  if (!hmacVerify(sig, payloadPart)) return null;

  let payload;
  try {
    const json = Buffer.from(payloadPart, 'base64url').toString('utf8');
    payload = JSON.parse(json);
  } catch {
    return null;
  }

  const uid = Number(payload.uid);
  const exp = Number(payload.exp);

  if (!Number.isInteger(uid) || uid <= 0) return null;
  if (!Number.isInteger(exp) || exp < nowSec() + 1) return null;

  return { uid, exp };
}

/* ------------------------------------------------------------------ *
 * Simple in-memory per-IP rate limiter                                *
 * ------------------------------------------------------------------ */

function createLimiter({ windowMs, max }) {
  const hits = new Map();
  return function allow(key) {
    const now = Date.now();
    const entry = hits.get(key);
    if (!entry || entry.resetAt <= now) {
      hits.set(key, { count: 1, resetAt: now + windowMs });
      return true;
    }
    entry.count += 1;
    return entry.count <= max;
  };
}

const limitConnect = createLimiter(CONNECT_RATE);
const limitEmit = createLimiter(EMIT_RATE);

/* ------------------------------------------------------------------ *
 * HTTP server + Socket.IO                                             *
 * ------------------------------------------------------------------ */

const app = express();
app.use(express.json({ limit: '64kb' }));

const server = http.createServer(app);

const io = new Server(server, {
  path: PATH,
  cors: {
    origin: CORS_ORIGINS,
    methods: ['GET', 'POST'],
    credentials: true,
  },
  serveClient: false, // we serve socket.io.js from the public CDN copy instead
  pingTimeout: 20_000,
  pingInterval: 25_000,
});

/* ------------------------------------------------------------------ *
 * Socket.IO: handshake authentication + rooms                         *
 * ------------------------------------------------------------------ */

io.use((socket, next) => {
  const ip = (socket.handshake.headers['x-forwarded-for'] || '').split(',')[0].trim()
    || socket.handshake.address || 'unknown';

  if (!limitConnect(ip)) {
    return next(new Error('too_many_connections'));
  }

  const token = socket.handshake.auth && socket.handshake.auth.token;
  const verified = verifyToken(token);

  if (!verified) {
    return next(new Error('unauthorized'));
  }

  socket.data.uid = verified.uid;
  return next();
});

io.on('connection', (socket) => {
  const uid = socket.data.uid;

  // Join a per-user room so PHP can push exactly to this user's sessions.
  socket.join(`user:${uid}`);

  // Notify this client immediately with the current local server time so it can
  // sanity-check heartbeat / latency.
  socket.emit('realtime:ready', { t: nowSec() });

  // Typing indicator: client sends { to: recipientId }, we relay to recipient.
  socket.on('user:typing', (data) => {
    const toId = Number(data && data.to);
    if (!Number.isInteger(toId) || toId <= 0) return;
    io.to(`user:${toId}`).emit('realtime:event', {
      event: 'user.typing',
      payload: { from: uid },
    });
  });

  socket.on('disconnect', () => {
    for (const room of socket.rooms) {
      socket.leave(room);
    }
  });
});

/* ------------------------------------------------------------------ *
 * PHP -> Node push endpoint (HMAC signed)                             *
 * ------------------------------------------------------------------ */

/**
 * Expected body:
 * {
 *   event:   'friend.request' | 'friend.accepted' | ...,
 *   userIds: [ number, ... ],
 *   payload: { ... safe JSON ... },
 *   ts:      number (unix seconds, must be within EMIT_SKEW of now),
 *   sig:     string  HMAC-SHA256(secret, "<ts>\n<event>\n<userIds>|<payload>")
 * }
 *
 * The signature covers the whole request, so an attacker without the secret
 * cannot replay with altered fields (apart from within the short skew window).
 */
app.post('/emit', (req, res) => {
  const ip = (req.headers['x-forwarded-for'] || '').split(',')[0].trim()
    || req.socket.remoteAddress || 'unknown';

  if (!limitEmit(ip)) {
    return res.status(429).json({ ok: false, error: 'rate_limited' });
  }

  const body = req.body;

  if (!body || typeof body !== 'object') {
    return res.status(400).json({ ok: false, error: 'bad_request' });
  }

  const { event, userIds, payload, ts, sig } = body;

  if (typeof event !== 'string' || !/^[a-z0-9_.-]{1,64}$/i.test(event)) {
    return res.status(400).json({ ok: false, error: 'bad_event' });
  }

  if (!Array.isArray(userIds) || userIds.length === 0 || userIds.length > 100) {
    return res.status(400).json({ ok: false, error: 'bad_user_ids' });
  }

  const targetIds = [];
  for (const id of userIds) {
    const n = Number(id);
    if (!Number.isInteger(n) || n <= 0) return res.status(400).json({ ok: false, error: 'bad_user_id' });
    targetIds.push(n);
  }

  if (typeof payload !== 'object' || payload === null) {
    return res.status(400).json({ ok: false, error: 'bad_payload' });
  }

  const t = Number(ts);
  if (!Number.isInteger(t)) {
    return res.status(400).json({ ok: false, error: 'bad_ts' });
  }

  if (Math.abs(nowSec() - t) > EMIT_SKEW) {
    return res.status(400).json({ ok: false, error: 'bad_ts_skew' });
  }

  const canonical = `${t}\n${event}\n${targetIds.join(',')}\n${JSON.stringify(payload)}`;
  if (typeof sig !== 'string' || !hmacVerify(sig, canonical)) {
    return res.status(403).json({ ok: false, error: 'bad_signature' });
  }

  // Deliver to each recipient user's room (may have several sockets open).
  for (const id of targetIds) {
    const room = `user:${id}`;
    const sockets = io.sockets.adapter.rooms.get(room);
    const count = sockets ? sockets.size : 0;
    if (count === 0) continue; // nobody online -> skip
    io.to(room).emit('realtime:event', {
      event,
      userIds: targetIds,
      payload,
      ts: t,
    });
  }

  res.status(200).json({ ok: true });
});

/* ------------------------------------------------------------------ *
 * Health                                                            *
 * ------------------------------------------------------------------ */

app.get('/health', (req, res) => {
  res.status(200).json({ ok: true, uptime: process.uptime() });
});

/* ------------------------------------------------------------------ *
 * Bootstrap                                                           *
 * ------------------------------------------------------------------ */

server.listen(PORT, () => {
  console.log(`[realtime] listening on http://0.0.0.0:${PORT} path=${PATH}`);
});
