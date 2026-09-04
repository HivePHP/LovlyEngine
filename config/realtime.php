<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Realtime (Socket.IO) & notifications
|--------------------------------------------------------------------------
|
| HivePHP pushes server-side events to clients in real time through a small
| standalone Node.js Socket.IO server (see  realtime/ folder).
|
| Data flow:
|   1. PHP triggers an event (e.g. a friend request).
|   2. PHP persists a notification row and POSTs an HMAC-signed payload to the
|      Node server's  /emit  endpoint.
|   3. Node validates the signature and relays the event to the recipient's
|      socket room.
|   4. The recipient's connected client updates the bell and the sidebar badge.
|
| The Node server never talks to the database: every realtime message is
| authored and authorized by PHP.
*/

return [
    // Master switch: set to false to disable realtime delivery (the bell and
    // sidebar still work through plain HTTP polling makes a single request on
    // page load; live updates are simply skipped).
    'enabled' => (bool) env('REALTIME_ENABLED', true),

    // The Node/Socket.IO server that the BROWSER connects to.
    'server' => [
        'host' => env('REALTIME_HOST', '127.0.0.1'),
        'port' => (int) env('REALTIME_PORT', 3001),
        'path' => env('REALTIME_PATH', '/socket.io'),
    ],

    // Full URL the browser uses to reach Socket.IO (host + port + path).
    // Defaults to ws(s)://<host>:<port><path>.
    'public_url' => env('REALTIME_PUBLIC_URL', null),

    // Shared secret used to (a) sign per-user socket tokens and (b) sign the
    // PHP->Node emit requests. Keep it long and random. Overwrite in .env!
    'secret' => env('REALTIME_SECRET', 'change-me-put-a-long-random-string-here'),

    // Lifetime (seconds) of a per-user socket handshake token.
    'token_ttl' => (int) env('REALTIME_TOKEN_TTL', 300),

    // Location (relative to public/) of the socket.io client bundle. The
    // Node setup (install.js) copies socket.io-client's UMD bundle here.
    'client_source' => 'socketio/socket.io.js',

    // PHP -> Node push timeout, ms.
    'http_timeout' => (int) env('REALTIME_HTTP_TIMEOUT', 2000),

    // Notification delivery options.
    'notification' => [
        // Maximum list length returned to the bell.
        'max_items'  => (int) env('NOTIFICATION_MAX_ITEMS', 30),
        // How long (days) notifications are retained; older rows are pruned.
        'ttl_days'   => (int) env('NOTIFICATION_TTL_DAYS', 90),
        // How many unread counts are pre-loaded per page render.
        'max_unread' => 5,
    ],

    // Map of event names PHP may push; used to whitelist payload types.
    'events' => [
        'friend.toggled'  => 'friend',
        'friend.request'  => 'friend',
        'friend.accepted' => 'friend',
        'message.event'   => 'messages',
        'message.read'    => 'messages',
    ],
];
