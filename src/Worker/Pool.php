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
        exec("php worker.php > /dev/null 2>&1 &");
    }

}