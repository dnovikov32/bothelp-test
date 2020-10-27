<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Worker\Pool;

const LIMIT_WORKERS = 256;

$pool = new Pool;

while ($pool->getWorkersCount() < LIMIT_WORKERS) {
    $pool->runWorker();
}