<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;
use App\Worker\Worker;
use App\Worker\Logger;

$worker = new Worker(
    new Client([
        'host' => 'redis',
        'port' => 6379
    ]),
    new Logger(__DIR__ . '/../log/log.txt')
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