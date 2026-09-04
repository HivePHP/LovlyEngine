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
| Static assets (OldVK-style dependency-based loading)
|--------------------------------------------------------------------------
|
| Source files live in resources/assets/{css,js}. The build script
| (bin/build-assets.php) bundles & hashes them into public/assets/ and
| writes a manifest to storage/cache/assets/manifest.php.
|
| A page only declares the bundle it needs, e.g.:
|
|     $assets->usePage('profile');
|
| The system then resolves:  css => app, chrome, profile-page
|                            js  => js/pages/shell.js
|
| Each bundle is emitted as a single content-hashed URL, which makes
| long-lived browser/edge caching safe (see public/.htaccess).
*/

return [
    // Public base URL for built assets.
    'base_url' => '/assets',

    // Where built (hashed) files are written — under this path relative to public/.
    'public_dir' => 'public/assets',

    // Where the build manifest is stored.
    'manifest' => 'storage/cache/assets/manifest.php',

    // CSS bundles. Each bundle = an ordered list of source files (relative
    // to resources/assets/). They are concatenated and content-hashed.
    'css_bundles' => [
        // Global, loaded on every page: reset, layout, buttons, form primitives.
        'app' => [
            'css/base.css',
            'css/buttons.css',
            'css/forms.css',
        ],

        // App "shell" chrome — only on main.twig pages (header + sidebar).
        'chrome' => [
            'css/header.css',
            'css/sidebar.css',
        ],

        // Home (main_home.twig) screens — login & register share the layout.
        'home-layout' => [
            'css/home-layout.css',
        ],

        'login-page'    => ['css/pages/login.css'],
        'register-page' => ['css/pages/register.css'],
        'profile-page'  => ['css/pages/profile.css'],
        'edit-page'     => ['css/pages/edit-profile.css'],
        'albums-page'   => ['css/pages/albums.css'],
        'friends-page'  => ['css/pages/friends.css'],
        'messages-page' => ['css/pages/messages.css'],
        'notifications-page' => ['css/pages/notifications.css'],
        'documents-page' => ['css/pages/documents.css'],
    ],

    // Page assets. A page maps to the css bundles (in order) and the JS
    // entry modules it needs. JS entries are relative to resources/assets/.
    'pages' => [
        'login' => [
            'css' => ['app', 'home-layout', 'login-page'],
            'js'  => ['js/pages/page-transition.js', 'js/pages/login.js'],
        ],

        'register' => [
            'css' => ['app', 'home-layout', 'register-page'],
            'js'  => ['js/pages/page-transition.js', 'js/pages/register.js'],
        ],

        'profile' => [
            'css' => ['app', 'chrome', 'profile-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/profile.js'],
        ],

        'edit-profile' => [
            'css' => ['app', 'chrome', 'profile-page', 'edit-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/edit-profile.js'],
        ],

        'albums' => [
            'css' => ['app', 'chrome', 'profile-page', 'albums-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/albums.js'],
        ],

        'friends' => [
            'css' => ['app', 'chrome', 'friends-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/friends.js'],
        ],

        'messages' => [
            'css' => ['app', 'chrome', 'messages-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/messages.js'],
        ],

        'notifications' => [
            'css' => ['app', 'chrome', 'notifications-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/notifications.js'],
        ],

        'documents' => [
            'css' => ['app', 'chrome', 'documents-page'],
            'js'  => ['js/pages/shell.js', 'js/pages/page-transition.js', 'js/pages/documents.js'],
        ],
    ],
];
