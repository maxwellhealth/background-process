<?php

namespace BackgroundProcessTest;

use BackgroundProcess\Process;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $files = [
            dirname(__FILE__) . '/stdout.log',
            dirname(__FILE__) . '/stderr.log',
        ];

        array_map(function ($file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }, $files);
    }

    public function testProcess()
    {
        $taskPath = dirname(__FILE__) . '/test-task.php';
        $command = 'php ' . $taskPath;

        $pid = (new Process())
            ->withCommand($command)
            ->run();

        $this->assertTrue($this->isProcessRunning($pid));
    }

    public function testProcessWithInput()
    {
        $taskPath = dirname(__FILE__) . '/test-stdin-task.php';
        $command = 'php ' . $taskPath;
        $input = 'hello world!';
        $stdoutFile = dirname(__FILE__) . '/stdout.log';

        $pid = (new Process())
            ->withCommand($command)
            ->withInput($input)
            ->withStdoutFile($stdoutFile)
            ->run();

        $this->assertTrue($this->isProcessRunning($pid));
        $this->assertSame($input, trim($this->getFileContents($stdoutFile)));
    }

    public function testProcessStderr()
    {
        $taskPath = dirname(__FILE__) . '/test-stderr-task.php';
        $command = 'php ' . $taskPath;
        $input = 'hello world!';
        $stderrFile = dirname(__FILE__) . '/stderr.log';

        $pid = (new Process())
            ->withCommand($command)
            ->withInput($input)
            ->withStderrFile($stderrFile)
            ->run();

        $this->assertTrue($this->isProcessRunning($pid));
        $this->assertSame($input, trim($this->getFileContents($stderrFile)));
    }

    private function isProcessRunning($pid)
    {
        $cmd = sprintf('kill -0 %d 2>&1', $pid);

        return shell_exec($cmd) === null;
    }

    private function getFileContents($path)
    {
        $timeStart = microtime(true);
        $timeElapsed = 0;

        do {
            $contents = file_get_contents($path);

            if (!empty($contents)) {
                return $contents;
            }

            usleep(200000);
            $timeElapsed = microtime(true) - $timeStart;
        } while ($timeElapsed <= 1);
    }
}
