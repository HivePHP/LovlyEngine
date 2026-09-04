/**
 * install:client — copies the browser socket.io-client UMD bundle into the
 * PHP app's public assets so it is served from the same origin as the app
 * (no external CDN, single source of truth).
 *
 * Run after `npm install`:
 *
 *     npm run install:client
 *
 * The bundle is emitted once by socket.io-client in version 4.x under
 * node_modules/socket.io/client-dist/socket.io.js.
 */

const fs = require('fs');
const path = require('path');

const SRC = path.join(__dirname, 'node_modules', 'socket.io', 'client-dist', 'socket.io.js');
const DST_DIR = path.join(__dirname, '..', 'public', 'assets', 'socketio');
const DST = path.join(DST_DIR, 'socket.io.js');

if (!fs.existsSync(SRC)) {
  console.error(
    '[install:client] socket.io client bundle not found at:\n  ' + SRC +
    '\nRun `npm install` first (installs socket.io + its client).'
  );
  process.exit(1);
}

fs.mkdirSync(DST_DIR, { recursive: true });
fs.copyFileSync(SRC, DST);

console.log('[install:client] copied socket.io client -> ' + DST);
