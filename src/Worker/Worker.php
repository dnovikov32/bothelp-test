<?php

declare(strict_types=1);

namespace App\Worker;

use Predis\Client;

class Worker {

    /**
     * Имя ключа очереди всех событий.
     */
    const EVENTS_QUEUE_KEY_NAME = 'queue:events';

    /**
     * Имя ключа для хранения массива всех событий аккаунта.
     */
    const ACCOUNT_EVENTS_KEY_NAME = 'account:%d';

    /**
     * Имя ключа мьютекса аккаунта.
     */
    const LOCK_KEY_NAME = 'lock:%d';

    /**
     * ID процесса.
     */
    public int $id = 0;

    /**
     * Экземпляр клиента Redis.
     */
    private Client $redis;

    /**
     * Экземпляр вспомогательного класса логгера.
     */
    private Logger $logger;

    /**
     * Текущее событие обработчика из очереди всех событий.
     *
     * ```php
     * [
     *     'accountId' => 851,
     *     'eventId' => 1
     * ]
     * ```
     */
    private array $event = [];

    /**
     * Массив всех ID событий аккаунта.
     * Семафор для правильного порядка обработки событий.
     */
    private array $queue = [];

    public function __construct(Client $redis, Logger $logger)
    {
        $this->id = getmypid();
        $this->redis = $redis;
        $this->logger = $logger;

        $this->initEvent();
    }

    /**
     * Читает событие из общей очереди и добавляет его в массив событий аккаунта.
     */
    private function initEvent()
    {
        $this->event = (array) json_decode($this->redis->lpop(self::EVENTS_QUEUE_KEY_NAME));

        $this->redis->sadd(sprintf(
            self::ACCOUNT_EVENTS_KEY_NAME, $this->event['accountId']
        ), [$this->event['eventId']]);
    }

    /**
     * Возвращает имя мьютекса.
     */
    private function getLockKey(): string
    {
        return sprintf(self::LOCK_KEY_NAME, (int) $this->event['accountId']);
    }

    /**
     * Имеет ли объект необработанное событие.
     */
    public function hasJob(): bool
    {
        return !empty($this->event);
    }

    /**
     * Устанавливает блокировку.
     */
    public function lock(): bool
    {
        return (bool) $this->redis->set($this->getLockKey(), 1, 'EX', 10, 'NX');
    }

    /**
     * Обработчик события.
     */
    public function execute(): bool
    {
        $this->queue = $this->redis->smembers(sprintf(
            self::ACCOUNT_EVENTS_KEY_NAME, $this->event['accountId']
        ));

        $this->queue = array_map('intval', $this->queue);
        sort($this->queue);

        if ($this->queue[0] !== $this->event['eventId']) {
            return false;
        }

        $this->logger->log(sprintf(
            "worker %d: account %d event %d",
            $this->id, $this->event['accountId'], $this->event['eventId']
        ));

        sleep(1);

        return true;
    }

    /**
     * Удаляет событие из очереди аккаунта и снимает блокировку.
     */
    public function delete(): bool
    {
        unset($this->queue[0]);

        $this->redis->srem(sprintf(
            self::ACCOUNT_EVENTS_KEY_NAME, $this->event['accountId']
        ), $this->event['eventId']);

        $this->release();
        $this->event = [];

        return true;
    }

    /**
     * Снимает блокировку.
     */
    public function release(): void
    {
        $this->redis->del($this->getLockKey());
    }

}