<?php

require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($className) {
include __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
});