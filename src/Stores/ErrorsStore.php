<?php

namespace HT\Stores;

use HT\Events\Error;

/**
 *
 * Registry for captured the Errors/Excpetions
 *
 */
class ErrorsStore extends Store
{
    /**
     * Register an Error Event
     *
     * @param \HT\Events\Error $error
     *
     * @return void
     */
    public function register(Error $error)
    {
        $this->store[] = $error;
    }
}
