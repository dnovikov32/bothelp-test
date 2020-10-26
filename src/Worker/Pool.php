<?php

declare(strict_types=1);

namespace App\Worker;

class Pool
{
    const LIMIT_WORKERS = 10;

    public function getWorkersCount(): int
    {
        return (int) exec('ps aux | grep -v grep | grep -c "worker.php"');
    }

    public function runWorker(): void
    {
        exec("php worker.php > /dev/null 2>&1 &");
    }

}