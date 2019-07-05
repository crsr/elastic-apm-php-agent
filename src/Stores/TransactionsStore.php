<?php

namespace HT\Stores;

use HT\Events\Transaction;
use HT\Exception\Transaction\DuplicateTransactionNameException;

/**
 *
 * Store for the Transaction Events
 *
 */
class TransactionsStore extends Store
{
    /**
     * Register a Transaction
     *
     * @throws \HT\Exception\Transaction\DuplicateTransactionNameException
     *
     * @param \HT\Events\Transaction $transaction
     *
     * @return void
     */
    public function register(Transaction $transaction)
    {
        $name = $transaction->getTransactionName();

        // Do not override the
        if (isset($this->store[$name]) === true) {
            throw new DuplicateTransactionNameException($name);
        }

        // Push to Store
        $this->store[$name] = $transaction;
    }

    /**
     * Fetch a Transaction from the Store
     *
     * @param string $name
     *
     * @return mixed: \HT\Events\Transaction | null
     */
    public function fetch($name)
    {
        return isset($this->store[$name]) ? $this->store[$name] : null;
    }

    /**
     * Serialize the Transactions Events Store
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_values($this->store);
    }
}
