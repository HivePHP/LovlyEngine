<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

HivePHP\Support\Dotenv::load(BASE_PATH);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): void {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(static function (Throwable $e): void {
    $log = sprintf(
        "[%s] %s in %s:%d\n%s\n\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );

    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    file_put_contents($logDir . '/error.log', $log, FILE_APPEND);

    http_response_code(500);
    header_remove('X-Powered-By');
    header('Content-Type: application/json; charset=utf-8');

    $debug = filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

    echo json_encode([
        'status'  => 'error',
        'message' => $debug ? $e->getMessage() : 'Internal server error',
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

use HivePHP\Bootstrap;
use HivePHP\Support\Container;

$container = new Container();
$app = new Bootstrap($container);
$app->run();
