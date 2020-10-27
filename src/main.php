<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Worker\Pool;

const LIMIT_WORKERS = 256;

$pool = new Pool;

echo "Start main process\n";

while ($pool->getWorkersCount() < LIMIT_WORKERS) {
    echo sprintf("Workers count: %d\n", $pool->getWorkersCount());
    $pool->runWorker();
}