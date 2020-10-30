<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;
use App\Worker\Worker;
use App\Worker\Logger;

(new \Dotenv\Dotenv(__DIR__ . '/../'))->load();

$queue = getenv('EVENTS_QUEUE_KEY_NAME');

$worker = new Worker(
    new Client([
        'host' => getenv('REDIS_HOST'),
        'port' => getenv('REDIS_PORT'),
    ]),
    new Logger(__DIR__ . '/../log/log.txt'),
    $queue
);

try {

    while ($worker->hasJob()) {

        if (! $worker->lock()) {
            usleep(300);
            continue;
        }

        if ($worker->execute()) {
            $worker->delete();
        } else {
            $worker->release();
        }
    }

} catch (Throwable $e) {
    $worker->release();
}

exit;