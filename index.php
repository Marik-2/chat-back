<?php

use App\Server;

require_once __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    die('Only cli');
}

$server = new Server();
$server->start();
