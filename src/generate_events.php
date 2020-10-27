<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;

const LIMIT_ACCOUNTS = 1000;
const LIMIT_EVENTS = 10000;
const LIMIT_ACCOUNT_EVENTS = 10;

$redis = new Client([
    'host' => 'redis',
    'port' => 6379,
]);

$redis->del('queue:events');

$eventsCount = 0;

while ($eventsCount < LIMIT_EVENTS) {
    $account = mt_rand(1, LIMIT_ACCOUNTS);

    $eventsNumber = mt_rand(1, LIMIT_ACCOUNT_EVENTS);
    $events = [];

    for ($i = 1; $i <= $eventsNumber; $i++) {
        $events[] = json_encode([
            'accountId' => $account,
            'eventId' => $i,
        ]);
    }

    $redis->rpush('queue:events', $events);

    $eventsCount += $eventsNumber;

    echo sprintf("Added account %d events %s\n", $account, $eventsNumber);
}

echo sprintf("Generated %d events\n", $eventsCount);