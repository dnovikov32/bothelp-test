<?php

declare(strict_types=1);

namespace App\Worker;

use Predis\Client;

class Worker {

    /**
     * Имя ключа очереди всех событий.
     */
    const QUEUE_EVENTS = 'queue:events';

    /**
     * Имя ключа для хранения массива всех событий аккаунта.
     */
    const QUEUE_ACCOUNT_EVENTS = 'account:%d';

    /**
     * Имя ключа мьютекса аккаунта.
     */
    const LOCK_KEY_NAME = 'lock:%d';

    /**
     * ID процесса.
     */
    private int $id = 0;

    /**
     * Экземпляр клиента Redis.
     */
    private Client $redis;

    /**
     * Экземпляр вспомогательного класса логгера.
     */
    private Logger $logger;

    /**
     * Текущее событие процесса из очереди.
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
     * Массив всех событий аккаунта.
     * Используется для правильного порядка обработки событий.
     */
    private array $queue = [];

    public function __construct(Client $redis, Logger $logger)
    {
        $this->id = getmypid();
        $this->redis = $redis;
        $this->logger = $logger;
        $this->event = (array) json_decode($redis->lpop(self::QUEUE_EVENTS));
    }

    /**
     * Имеет ли объект необработанное событие.
     */
    public function hasJob(): bool
    {
        return !empty($this->event);
    }

    /**
     * Установлена ли блокировка.
     */
    public function hasLock(): bool
    {
        $this->logger->log("$this->id has lock" . $this->redis->get($this->getLockKey()));
        return (bool) $this->redis->get($this->getLockKey());
    }

    /**
     * Обработчик события.
     */
    public function execute(): bool
    {
//        $this->logger->log("$this->id lock before ");

        if (! $this->lock()) {
            return false;
        }

//        $this->logger->log("$this->id lock after");
//        $this->logger->log("$this->id " . print_r($this->event, true));

        if ($this->queue[0] !== $this->event['eventId']) {
            return false;
        }

        $this->logger->log(sprintf(
            "%d account %d event %d\n", $this->id, $this->event['accountId'], $this->event['eventId']
        ));

        sleep(1);

        return true;
    }

    /**
     * Устанавливает блокировку и добавляет событие в очередь аккаунта.
     */
    public function lock(): bool
    {
        $result = $this->redis->set($this->getLockKey(), true, 'EX', 10, 'NX');

//        $this->logger->log("$this->id lock result $result");

        if (! $result) {
            return false;
        }

        $this->queue = $this->redis->hgetall(sprintf(
            self::QUEUE_ACCOUNT_EVENTS, $this->event['accountId']
        ));

        // Добавляет новое событие в очередь аккаунта.
        if (!in_array($this->event['eventId'], $this->queue)) {
            array_push($this->queue, $this->event['eventId']);
            sort($this->queue);

            $this->setAccountQueue();
        }

        return true;
    }

    /**
     * Удаляет событие из объекта и из очереди аккаунта.
     */
    public function delete(): bool
    {
        unset($this->queue[0]);
        $this->setAccountQueue();
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

    /**
     * Обновляет значение очереди аккаунта.
     */
    private function setAccountQueue(): bool
    {
        if (! $this->queue) {
            $this->redis->del(sprintf(self::QUEUE_ACCOUNT_EVENTS, $this->event['accountId']));

            return true;
        }

        $this->redis->hmset(sprintf(
            self::QUEUE_ACCOUNT_EVENTS, $this->event['accountId']
        ), $this->queue);

        return true;
    }

    /**
     * Возвращает имя мьютекса.
     */
    private function getLockKey(): string
    {
        return sprintf(self::LOCK_KEY_NAME, (int) $this->event['accountId']);
    }

}