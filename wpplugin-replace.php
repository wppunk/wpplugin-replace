<?php

require_once __DIR__ . '/../../autoload.php';

$dir = realpath( __DIR__ . '/../../../' ) . '/';

$response = ( new \wppunk\PluginRename\Wizard() )->run();
echo 'Replacing plugin...' . PHP_EOL;
( new \wppunk\PluginRename\Replace( $dir, ...$response ) )->run();
echo 'Plugin variables were replaced.' . PHP_EOL;

