<?php

$envFile = __DIR__ . '/../.env';

if (file_exists($envFile)) {

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = array_pad(explode('=', $line, 2), 2, '');

        $_ENV[trim($key)] = trim($value);
    }

}