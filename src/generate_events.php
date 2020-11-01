<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Predis\Client;

(new Dotenv(__DIR__ . '/../'))->load();

$queue = getenv('EVENTS_QUEUE_KEY_NAME');
$limitEvents = (int) getenv('LIMIT_EVENTS');
$limitAccounts = (int) getenv('LIMIT_ACCOUNTS');
$limitAccountEvents = (int) getenv('LIMIT_ACCOUNT_EVENTS');
$eventsCount = 0;

$redis = new Client([
    'host' => getenv('REDIS_HOST'),
    'port' => getenv('REDIS_PORT'),
]);

$redis->del($queue);

$accounts = range(1, $limitAccounts * 2);
shuffle($accounts);

while ($accounts && $eventsCount < $limitEvents) {
    $accountId = array_pop($accounts);
    $accountEvents = mt_rand(1, $limitAccountEvents);
    $events = [];

    for ($i = 1; $i <= $accountEvents; $i++) {
        $events[] = json_encode([
            'accountId' => $accountId,
            'eventId' => $i,
        ]);
    }

    $redis->rpush($queue, $events);

    $eventsCount += $accountEvents;
    echo sprintf("Added account %d events %s\n", $accountId, $accountEvents);
}

echo sprintf("Generated %d events\n", $eventsCount);