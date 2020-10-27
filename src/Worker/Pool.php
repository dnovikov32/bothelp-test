<?php

declare(strict_types=1);

namespace App\Worker;

class Pool
{
    public function getWorkersCount(): int
    {
        return (int) exec('ps aux | grep -v grep | grep -c "worker.php"');
    }

    public function runWorker(): void
    {
        $script = __DIR__ . '/../worker.php';
        exec(sprintf("php %s > /dev/null 2>&1 &", $script));
    }

}