<?php

namespace HT\Events;


use Rhumsaa\Uuid\Uuid;

/**
 *
 * EventBean for occuring events such as Exceptions or Transactions
 *
 */
class EventBean
{
    /**
     * UUID
     *
     * @var string
     */
    private $id;

    /**
     * Error occurred on Timestamp
     *
     * @var string
     */
    private $timestamp;

    /**
     * Event Metadata
     *
     * @var array
     */
    private $meta = array(
        'result' => 200,
        'type'   => 'generic'
    );

    /**
     * Extended Contexts such as Custom and/or User
     *
     * @var array
     */
    private $contexts = array(
        'request'  => array(),
        'user'     => array(),
        'custom'   => array(),
        'env'      => array(),
        'tags'     => array(),
        'response' => array(
            'finished'     => true,
            'headers_sent' => true,
            'status_code'  => 200,
        ),
    );

    /**
     * Init the Event with the Timestamp and UUID
     *
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/3
     *
     * @param array $contexts
     */
    public function __construct(array $contexts)
    {
        // Generate Random UUID
        $this->id = Uuid::uuid4()->toString();

        // Merge Initial Context
        $this->contexts = array_merge($this->contexts, $contexts);

        // Get UTC timestamp of Now
        $timestamp = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $timestamp->setTimeZone(new \DateTimeZone('UTC'));
        $this->timestamp = $timestamp->format('Y-m-d\TH:i:s.u\Z');
    }

    /**
     * Get the Event Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the Event's Timestamp
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set the Transaction Meta data
     *
     * @param array $meta
     *
     * @return void
     */
    final public function setMeta(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);
    }

    /**
     * Set Meta data of User Context
     *
     * @param array $userContext
     */
    final public function setUserContext(array $userContext)
    {
        $this->contexts['user'] = array_merge($this->contexts['user'], $userContext);
    }

    /**
     * Set custom Meta data for the Transaction in Context
     *
     * @param array $customContext
     */
    final public function setCustomContext(array $customContext)
    {
        $this->contexts['custom'] = array_merge($this->contexts['custom'], $customContext);
    }

    /**
     * Set Transaction Response
     *
     * @param array $response
     */
    final public function setResponse(array $response)
    {
        $this->contexts['response'] = array_merge($this->contexts['response'], $response);
    }

    /**
     * Set Tags for this Transaction
     *
     * @param array $tags
     */
    final public function setTags(array $tags)
    {
        $this->contexts['tags'] = array_merge($this->contexts['tags'], $tags);
    }

    /**
     * Set Transaction Request
     *
     * @param array $request
     */
    final public function setRequest(array $request)
    {
        $this->contexts['request'] = array_merge($this->contexts['request'], $request);
    }

    /**
     * Generate request data
     *
     * @return array
     */
    final public function generateRequest()
    {
        $headers = getallheaders();
        $http_or_https = isset($_SERVER['HTTPS']) ? 'https' : 'http';

        // Build Context Stub
        $SERVER_PROTOCOL = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '';
        $remote_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) === true) {
            $remote_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return array(
            'http_version' => substr($SERVER_PROTOCOL, strpos($SERVER_PROTOCOL, '/')),
            'method'       => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'cli',
            'socket'       => array(
                'remote_address' => $remote_address,
                'encrypted'      => isset($_SERVER['HTTPS'])
            ),
            'response' => $this->contexts['response'],
            'url'          => array(
                'protocol' => $http_or_https,
                'hostname' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '',
                'port'     => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '',
                'pathname' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '',
                'search'   => '?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''),
                'full' => isset($_SERVER['HTTP_HOST']) ? $http_or_https . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : '',
            ),
            'headers' => array(
                'user-agent' => isset($headers['User-Agent']) ? $headers['User-Agent'] : '',
                'cookie'     => $this->getCookieHeader(isset($headers['Cookie']) ? $headers['Cookie'] : ''),
            ),
            'env' => (object)$this->getEnv(),
            'cookies' => (object)$this->getCookies(),
        );
    }

    /**
     * Get Type defined in Meta
     *
     * @return string
     */
    final protected function getMetaType()
    {
        return $this->meta['type'];
    }

    /**
     * Get the Result of the Event from the Meta store
     *
     * @return string
     */
    final protected function getMetaResult()
    {
        return (string)$this->meta['result'];
    }

    /**
     * Get the Environment Variables
     *
     * @link http://php.net/manual/en/reserved.variables.server.php
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/27
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/54
     *
     * @return array
     */
    final protected function getEnv()
    {
        $envMask = $this->contexts['env'];
        $env = empty($envMask)
            ? $_SERVER
            : array_intersect_key($_SERVER, array_flip($envMask));

        return $env;
    }

    /**
     * Get the cookies
     *
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/30
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/54
     *
     * @return array
     */
    final protected function getCookies()
    {
        $cookieMask = $this->contexts['cookies'];
        return empty($cookieMask)
            ? $_COOKIE
            : array_intersect_key($_COOKIE, array_flip($cookieMask));
    }

    /**
     * Get the cookie header
     *
     * @link https://github.com/philkra/elastic-apm-php-agent/issues/30
     *
     * @return string
     */
    final protected function getCookieHeader($cookieHeader)
    {
        $cookieMask = $this->contexts['cookies'];

        // Returns an empty string if cookies are masked.
        return empty($cookieMask) ? $cookieHeader : '';
    }

    /**
     * Get the Events Context
     *
     * @link https://www.elastic.co/guide/en/apm/server/current/transaction-api.html#transaction-context-schema
     *
     * @return array
     */
    final protected function getContext()
    {
        $context = array(
            'request' => empty($this->contexts['request']) ? $this->generateRequest() : $this->contexts['request']
        );

        // Add User Context
        if (empty($this->contexts['user']) === false) {
            $context['user'] = $this->contexts['user'];
        }

        // Add Custom Context
        if (empty($this->contexts['custom']) === false) {
            $context['custom'] = $this->contexts['custom'];
        }

        // Add Tags Context
        if (empty($this->contexts['tags']) === false) {
            $context['tags'] = $this->contexts['tags'];
        }

        return $context;
    }
}
