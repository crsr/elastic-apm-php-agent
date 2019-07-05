<?php

namespace PhilKra\Events;

final class DefaultEventFactory implements EventFactoryInterface
{
    /**
     * {@inheritdoc}
     * @return Error
     */
    public function createError(\Throwable $throwable, array $contexts)
    {
        return new Error($throwable, $contexts);
    }

    /**
     * {@inheritdoc}
     * @return Transaction
     */
    public function createTransaction($name, array $contexts, $start = null)
    {
        return new Transaction($name, $contexts, $start);
    }
}
