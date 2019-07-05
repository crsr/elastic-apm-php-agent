<?php

namespace PhilKra\Events;

interface EventFactoryInterface
{
    /**
     * Creates a new error.
     * 
     * @param \Throwable $throwable
     * @param array      $contexts
     *
     * @return Error
     */
    public function createError(\Throwable $throwable, array $contexts);

    /**
     * Creates a new transaction
     *
     * @param string $name
     * @param array  $contexts
     *
     * @return Transaction
     */
    public function createTransaction($name, array $contexts, $start = null);
}
