<?php

require __DIR__ . '/../vendor/autoload.php';

$envFileName = realpath(__DIR__ . '/../.env');
if (is_readable($envFileName)) {
    $envContents = file_get_contents($envFileName);
    $environment = explode("\n", trim($envContents));
    foreach ($environment as $line) {
        putenv(trim($line));
    }
}
