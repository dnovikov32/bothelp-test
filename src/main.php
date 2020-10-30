<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;
use App\Worker\Pool;

(new \Dotenv\Dotenv(__DIR__ . '/../'))->load();

$queue = getenv('EVENTS_QUEUE_KEY_NAME');
$limitWorkers = getenv('LIMIT_WORKERS');

$pool = new Pool(
    new Client([
        'host' => getenv('REDIS_HOST'),
        'port' => getenv('REDIS_PORT'),
    ]),
    $queue
);

echo "Start main process\n";

while (true) {
    if (! $pool->hasJob()) {
        echo "Waiting\n";
        sleep(3);
        continue;
    }

    if ($pool->getWorkersCount() < $limitWorkers) {
        $pool->runWorker();
        echo sprintf("Workers count: %d\n", $pool->getWorkersCount());
    }
}