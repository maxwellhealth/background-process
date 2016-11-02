<?php

namespace BackgroundProcess;

class Process
{
    private $command = '';
    private $input = '';

    private $stdoutFile = '/dev/null';
    private $stderrFile = '';

    public function withCommand($command)
    {
        $command = (string) $command;

        if (empty($command)) {
            throw new \InvalidArgumentException('$command cannot be empty');
        }

        $new = clone $this;
        $new->command = (string) $command;

        return $new;
    }

    public function withInput($input)
    {
        $new = clone $this;
        $new->input = $input;

        return $new;
    }

    public function withStdoutFile($stdoutFile)
    {
        $new = clone $this;
        $new->stdoutFile = (string) $stdoutFile;

        return $new;
    }

    public function withStderrFile($stderrFile)
    {
        $new = clone $this;
        $new->stderrFile = (string) $stderrFile;

        return $new;
    }

    public function run()
    {
        if (empty($this->command)) {
            throw new \InvalidArgumentException(__CLASS__ . '::withCommand() must be called before calling ' . __CLASS__ . '::run()');
        }

        $command = $this->command;
        $input = $this->input;
        $stdoutFile = $this->stdoutFile;
        $stderrFile = $this->stderrFile;

        if (empty($stderrFile)) {
            $stderrFile = '&1';
        }

        $command = sprintf('%s > %s 2>%s', $command, $stdoutFile, $stderrFile);

        if ($input) {
            $command = $command . ' <<< $(printf "%s\n" ' . escapeshellarg($input) . ')';
        }

        $command .= ' & echo $!';

        $scriptPath = '/tmp/' . uniqid() . '.sh';
        file_put_contents($scriptPath, "$command");

        $pid = (int) trim(shell_exec('bash ' . $scriptPath));

        unlink($scriptPath);

        $pid = (int) trim(shell_exec('bash -c ' . $command));

        return $pid;
    }
}
