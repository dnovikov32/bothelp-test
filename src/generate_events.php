<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Predis\Client;

(new \Dotenv\Dotenv(__DIR__ . '/../'))->load();

$limitEvents = (int) getenv('LIMIT_EVENTS');
$limitAccounts = (int) getenv('LIMIT_ACCOUNTS');
$limitAccountEvents = (int) getenv('LIMIT_ACCOUNT_EVENTS');
$queue = getenv('EVENTS_QUEUE_KEY_NAME');
$eventsCount = 0;

$redis = new Client([
    'host' => getenv('REDIS_HOST'),
    'port' => getenv('REDIS_PORT'),
]);

$redis->del($queue);

while ($eventsCount < $limitEvents) {
    $account = mt_rand(1, $limitAccounts);

    $eventsNumber = mt_rand(1, $limitAccountEvents);
    $events = [];

    for ($i = 1; $i <= $eventsNumber; $i++) {
        $events[] = json_encode([
            'accountId' => $account,
            'eventId' => $i,
        ]);
    }

    $redis->rpush($queue, $events);

    $eventsCount += $eventsNumber;

    echo sprintf("Added account %d events %s\n", $account, $eventsNumber);
}

echo sprintf("Generated %d events\n", $eventsCount);