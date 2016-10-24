<?php

require_once 'vendor/autoload.php';

use BackgroundProcess\Process;

$pid = (new Process())
    ->withCommand('php tests/test-task.php')
    ->run();

echo 'Started process with PID ' . $pid . PHP_EOL;
