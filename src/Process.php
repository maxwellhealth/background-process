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

        $tmpFile = tmpfile();

        // Get the temporary file's header/meta data from the file
        // handle ($tmpFile) using stream_get_meta_data() which will return an
        // array containing meta data like filename.
        //
        // We use this function because tmpfile() returns a file handle, not
        // a filename string.
        $meta = stream_get_meta_data($tmpFile);

        // Assign the URI/filename to a variable which will be used when
        // executing the background process.
        $tmpFilename = $meta['uri'];

        fwrite($tmpFile, "#!/bin/bash\n\n$command");

        $pid = (int) trim(shell_exec('bash ' . $tmpFilename));

        fclose($tmpFile);

        return $pid;
    }
}
