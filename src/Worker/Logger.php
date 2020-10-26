<?php

declare(strict_types=1);

namespace App\Worker;

class Logger
{
    private string $file = '';

    public function __construct(string $file)
    {
        $this->file = $file;
        file_put_contents($this->file, '');
    }

    public function log(string $message)
    {
        file_put_contents($this->file, sprintf("%s\n", $message),  FILE_APPEND );
    }
}