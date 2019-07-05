<?php

namespace HT\Events;

use HT\Helper\Timer;
use HT\Serializers\JsonSerializable;

/**
 *
 * Abstract Transaction class for all inheriting Transactions
 *
 * @link https://www.elastic.co/guide/en/apm/server/master/transaction-api.html
 *
 */
class Transaction extends EventBean implements JsonSerializable
{
    /**
     * Transaction Name
     *
     * @var string
     */
    private $name;

    /**
     * Transaction Timer
     *
     * @var \HT\Helper\Timer
     */
    private $timer;

    /**
     * Summary of this Transaction
     *
     * @var array
     */
    private $summary = array(
        'duration'  => 0.0,
        'backtrace' => null,
        'headers'   => array()
    );

    /**
     * The spans for the transaction
     *
     * @var array
     */
    private $spans = array();

    /**
     * Backtrace Depth
     *
     * @var int
     */
    private $backtraceLimit = 0;

    /**
    * Create the Transaction
    *
    * @param string $name
    * @param array $contexts
    */
    public function __construct($name, array $contexts, $start = null)
    {
        parent::__construct($contexts);
        $this->setTransactionName($name);
        $this->timer = new Timer($start);
    }

    /**
    * Start the Transaction
    *
    * @return void
    */
    public function start()
    {
        $this->timer->start();
    }

    /**
     * Stop the Transaction
     *
     * @param integer|null $duration
     *
     * @return void
     */
    public function stop($duration = null)
    {
        // Stop the Timer
        $this->timer->stop();

        // Store Summary
        $this->summary['duration']  = !empty($duration) ? $duration : round($this->timer->getDurationInMilliseconds(), 3);
        $this->summary['headers']   = (function_exists('xdebug_get_headers') === true) ? xdebug_get_headers() : array();
        $this->summary['backtrace'] = debug_backtrace($this->backtraceLimit);
    }

    /**
    * Set the Transaction Name
    *
    * @param string $name
    *
    * @return void
    */
    public function setTransactionName($name)
    {
        $this->name = $name;
    }

    /**
    * Get the Transaction Name
    *
    * @return string
    */
    public function getTransactionName()
    {
        return $this->name;
    }

    /**
    * Get the Summary of this Transaction
    *
    * @return array
    */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set the spans for the transacton
     *
     * @param array $spans
     *
     * @return void
     */
    public function setSpans(array $spans)
    {
        $this->spans = $spans;
    }

    /**
     * Set the Max Depth/Limit of the debug_backtrace method
     *
     * @link http://php.net/manual/en/function.debug-backtrace.php
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/55
     *
     * @param int $limit [description]
     */
    public function setBacktraceLimit($limit)
    {
        $this->backtraceLimit = $limit;
    }

    /**
     * Get the spans from the transaction
     *
     * @return array
     */
    private function getSpans()
    {
        return $this->spans;
    }

    /**
    * Serialize Transaction Event
    *
    * @return array
    */
    public function jsonSerialize()
    {
        return array(
          'id'        => $this->getId(),
          'timestamp' => $this->getTimestamp(),
          'name'      => $this->getTransactionName(),
          'duration'  => $this->summary['duration'],
          'type'      => $this->getMetaType(),
          'result'    => $this->getMetaResult(),
          'context'   => $this->getContext(),
          'spans'     => $this->getSpans(),
          'processor' => array(
              'event' => 'transaction',
              'name'  => 'transaction',
          )
        );
    }
}
