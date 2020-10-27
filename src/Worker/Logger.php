<?php

declare(strict_types=1);

namespace App\Worker;

/**
 * Конечно я бы взял готовое решение (monolog).
 */
class Logger
{
    private string $file = '';

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function log(string $message)
    {
        file_put_contents($this->file, sprintf("%s\n", $message),  FILE_APPEND);
    }
}