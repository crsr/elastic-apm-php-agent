<?php

namespace PhilKra\Stores;

use PhilKra\Serializers\JsonSerializable;

/**
 *
 * Registry for captured the Events
 *
 */
class Store implements JsonSerializable
{
    /**
     * Set of Events
     *
     * @var array of \PhilKra\Events\EventBean
     */
    protected $store = array();

    /**
     * Get all Registered Errors
     *
     * @return array of \PhilKra\Events\EventBean
     */
    public function listing()
    {
        return $this->store;
    }

    /**
     * Is the Store Empty ?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->store);
    }

    /**
     * Empty the Store
     *
     * @return void
     */
    public function reset()
    {
        $this->store = array();
    }

    /**
     * Serialize the Events Store
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->store;
    }
}
