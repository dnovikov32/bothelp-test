<?php

declare(strict_types=1);

namespace App\Worker;

use Predis\Client;

class Pool
{
    /**
     * Экземпляр клиента Redis.
     */
    private Client $redis;

    /**
     * Имя ключа очереди всех событий.
     */
    private string $queue;

    public function __construct(Client $redis, string $queue)
    {
        $this->redis = $redis;
        $this->queue = $queue;
    }

    public function hasJob(): bool
    {
        return (bool) $this->redis->llen($this->queue);
    }

    /**
     * Возвращает количество активных процессов обработчиков.
     */
    public function getWorkersCount(): int
    {
        return (int) exec('ps aux | grep -v grep | grep -c "worker.php"');
    }

    /**
     * Запускает новый процесс обработчик.
     */
    public function runWorker(): void
    {
        $script = __DIR__ . '/../worker.php';
        exec(sprintf("php %s > /dev/null 2>&1 &", $script));
    }

}