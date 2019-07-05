<?php

namespace HT\Serializers;

use HT\Stores\TransactionsStore;
use HT\Helper\Config;

/**
 *
 * Convert the Registered Transactions to JSON Schema
 *
 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
 *
 */
class Transactions extends Entity implements JsonSerializable
{
    /**
     * @var \HT\Stores\TransactionsStore
     */
    private $store;

    /**
     * @param ErrorsStore $store
     */
    public function __construct(Config $config, TransactionsStore $store)
    {
        parent::__construct($config);
        $this->store = $store;
    }

    /**
     * Serialize Transactions Data to JSON "ready" Array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getSkeleton() + array(
                'transactions' => $this->store
            );
    }
}
