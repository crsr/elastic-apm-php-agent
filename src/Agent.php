<?php

namespace HT;

use HT\Events\DefaultEventFactory;
use HT\Events\EventFactoryInterface;
use HT\Stores\ErrorsStore;
use HT\Stores\TransactionsStore;
use HT\Events\Transaction;
use HT\Helper\Timer;
use HT\Helper\Config;
use HT\Middleware\Connector;
use HT\Exception\Transaction\UnknownTransactionException;

/**
 *
 * APM Agent
 *
 * @link https://www.elastic.co/guide/en/apm/server/master/transaction-api.html
 *
 */
class Agent
{
    /**
     * Agent Version
     *
     * @var string
     */
    const VERSION = '6.5.4';

    /**
     * Agent Name
     *
     * @var string
     */
    const NAME = 'elastic-php';

    /**
     * Config Store
     *
     * @var \HT\Helper\Config
     */
    private $config;

    /**
     * Transactions Store
     *
     * @var \HT\Stores\TransactionsStore
     */
    private $transactionsStore;

    /**
     * Error Events Store
     *
     * @var \HT\Stores\ErrorsStore
     */
    private $errorsStore;

    /**
     * Apm Timer
     *
     * @var \HT\Helper\Timer
     */
    private $timer;

    /**
     * Common/Shared Contexts for Errors and Transactions
     *
     * @var array
     */
    private $sharedContext = array(
        'user'   => array(),
        'custom' => array(),
        'tags'   => array()
    );

    /**
     * @var EventFactoryInterface
     */
    private $eventFactory;

    /**
     * Setup the APM Agent
     *
     * @param array                 $config
     * @param array                 $sharedContext Set shared contexts such as user and tags
     * @param EventFactoryInterface $eventFactory  Alternative factory to use when creating event objects
     *
     * @return void
     */
    public function __construct(array $config, array $sharedContext = array(), EventFactoryInterface $eventFactory = null, TransactionsStore $transactionsStore = null, ErrorsStore $errorsStore = null)
    {
        // Init Agent Config
        $this->config = new Config($config);

        // Use the custom event factory or create a default one
        $this->eventFactory = $eventFactory ? $eventFactory : new DefaultEventFactory();

        // Init the Shared Context
        $this->sharedContext['user']   = isset($sharedContext['user']) ? $sharedContext['user'] : array();
        $this->sharedContext['custom'] = isset($sharedContext['custom']) ? $sharedContext['custom'] : array();
        $this->sharedContext['tags']   = isset($sharedContext['tags']) ? $sharedContext['tags'] : array();

        // Let's misuse the context to pass the environment variable and cookies
        // config to the EventBeans and the getContext method
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/27
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/30
        $this->sharedContext['env'] = $this->config->get('env', array());
        $this->sharedContext['cookies'] = $this->config->get('cookies', array());

        // Initialize Event Stores
        $this->transactionsStore = $transactionsStore ? $transactionsStore : new TransactionsStore();
        $this->errorsStore       = $errorsStore ? $errorsStore : new ErrorsStore();

        // Start Global Agent Timer
        $this->timer = new Timer();
        $this->timer->start();
    }

    /**
     * Start the Transaction capturing
     *
     * @throws \HT\Exception\Transaction\DuplicateTransactionNameException
     *
     * @param string $name
     * @param array  $context
     *
     * @return Transaction
     */
    public function startTransaction($name, array $context = array(), $start = null)
    {
        // Create and Store Transaction
        $this->transactionsStore->register(
            $this->eventFactory->createTransaction($name, array_replace_recursive($this->sharedContext, $context), $start)
        );

        // Start the Transaction
        $transaction = $this->transactionsStore->fetch($name);

        if (null === $start) {
            $transaction->start();
        }

        return $transaction;
    }

    /**
     * Stop the Transaction
     *
     * @throws \HT\Exception\Transaction\UnknownTransactionException
     *
     * @param string $name
     * @param array $meta, Def: []
     *
     * @return void
     */
    public function stopTransaction($name, array $meta = array())
    {
        $this->getTransaction($name)->setBacktraceLimit($this->config->get('backtraceLimit', 0));
        $this->getTransaction($name)->stop();
        $this->getTransaction($name)->setMeta($meta);
    }

    /**
     * Get a Transaction
     *
     * @throws \HT\Exception\Transaction\UnknownTransactionException
     *
     * @param string $name
     *
     * @return Transaction
     */
    public function getTransaction($name)
    {
        $transaction = $this->transactionsStore->fetch($name);
        if ($transaction === null) {
            throw new UnknownTransactionException($name);
        }

        return $transaction;
    }

    /**
     * Register a Thrown Exception, Error, etc.
     *
     * @link http://php.net/manual/en/class.throwable.php
     *
     * @param \Throwable $thrown
     * @param array      $context
     *
     * @return void
     */
    public function captureThrowable(\Throwable $thrown, array $context = array())
    {
        $this->errorsStore->register(
            $this->eventFactory->createError($thrown, array_replace_recursive($this->sharedContext, $context))
        );
    }

    /**
     * Get the Agent Config
     *
     * @return \HT\Helper\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Send Data to APM Service
     *
     * @link https://github.com/philkra/elastic-apm-laravel/issues/22
     * @link https://github.com/philkra/elastic-apm-laravel/issues/26
     *
     * @return bool
     */
    public function send()
    {
        // Is the Agent enabled ?
        if ($this->config->get('active') === false) {
            $this->errorsStore->reset();
            $this->transactionsStore->reset();
            return true;
        }

        $connector = new Connector($this->config);
        $status = true;

        // Commit the Errors
        if ($this->errorsStore->isEmpty() === false) {
            $status = $status && $connector->sendErrors($this->errorsStore);
            if ($status === true) {
                $this->errorsStore->reset();
            }
        }

        // Commit the Transactions
        if ($this->transactionsStore->isEmpty() === false) {
            $status = $status && $connector->sendTransactions($this->transactionsStore);
            if ($status === true) {
                $this->transactionsStore->reset();
            }
        }

        return $status;
    }
}
