<?php
declare(strict_types=1);

// Объявляем helpers константы
define('ROOT', dirname(__DIR__));

// Автозагрузка классов
require ROOT . '/vendor/autoload.php';

use HivePHP\Bootstrap;
use HivePHP\Container;

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$container = new Container();
$app = new Bootstrap($container);
$app->run();