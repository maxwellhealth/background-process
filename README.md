# background-process

[![Build Status](https://travis-ci.org/maxwellhealth/background-process.svg?branch=master)](https://travis-ci.org/maxwellhealth/background-process)

A small PHP library to run background processes.

### Basic Usage

From example.php:

```php
<?php

require_once 'vendor/autoload.php';

use BackgroundProcess\Process;

$pid = (new Process())
    ->withCommand('php tests/test-task.php')
    ->run();

echo 'Started process with PID ' . $pid . PHP_EOL;

```

```
user@host:~/background-process$ php example.php
Started process with PID 2129
user@host:~/background-process$ ps
  PID TTY           TIME CMD
 1706 ttys001    0:00.03 -bash
 2129 ttys001    0:00.03 php tests/test-task.php
user@host:~/background-process$
```

### Capturing stdout and stderr
```php
$pid = (new Process())
    ->withCommand('php tests/test-task.php')
    ->withStdoutFile('/tmp/stdout.log')
    ->withStderrFile('/tmp/stderr.log')
    ->run();
```

### Writing to stdin

```php
$pid = (new Process())
    ->withCommand('php tests/test-task.php')
    ->withInput('hello world!')
    ->run();
```
