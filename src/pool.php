<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Worker\Pool;

$pool = new Pool;

while ($pool->getWorkersCount() < Pool::LIMIT_WORKERS) {
    $pool->runWorker();
}