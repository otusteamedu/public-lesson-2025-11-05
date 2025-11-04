<?php

namespace App\Application\CQRS;

use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

trait CQRSTrait
{
    private readonly MessageBusInterface $commandBus;
    private readonly MessageBusInterface $queryBus;

    /**
     * @param mixed $message
     * @return mixed
     *
     * @throws ExceptionInterface
     */
    private function handleCommand(mixed $message): mixed
    {
        $envelope = $this->commandBus->dispatch($message);

        return $envelope->last(HandledStamp::class)->getResult();
    }

    /**
     * @param mixed $query
     * @return mixed
     *
     * @throws ExceptionInterface
     */
    private function handleQuery(mixed $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}