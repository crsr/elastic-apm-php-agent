<?php

namespace HT\Middleware;

use HT\Agent;
use HT\Stores\ErrorsStore;
use HT\Stores\TransactionsStore;
use HT\Serializers\Errors;
use HT\Serializers\Transactions;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

/**
 *
 * Connector which Transmits the Data to the Endpoints
 *
 */
class Connector
{
    /**
     * Agent Config
     *
     * @var \HT\Helper\Config
     */
    private $config;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @param \HT\Helper\Config $config
     */
    public function __construct(\HT\Helper\Config $config)
    {
        $this->config = $config;

        $this->configureHttpClient();
    }

    /**
     * Create and configure the HTTP client
     *
     * @return void
     */
    private function configureHttpClient()
    {
        $httpClientDefaults = array(
            'timeout' => $this->config->get('timeout'),
        );

        $httpClientConfig = $this->config->get('httpClient') !== null ? $this->config->get('httpClient') : array();

        $this->client = new Client(array_merge($httpClientDefaults, $httpClientConfig));
    }

    /**
     * Push the Transactions to APM Server
     *
     * @param \HT\Stores\TransactionsStore $store
     *
     * @return bool
     */
    public function sendTransactions(TransactionsStore $store)
    {
        $request = new Request(
            'POST',
            $this->getEndpoint('transactions'),
            $this->getRequestHeaders(),
            json_encode(new Transactions($this->config, $store))
        );

        $response = $this->client->send($request);
        return ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    /**
     * Push the Errors to APM Server
     *
     * @param \HT\Stores\ErrorsStore $store
     *
     * @return bool
     */
    public function sendErrors(ErrorsStore $store)
    {
        $request = new Request(
            'POST',
            $this->getEndpoint('errors'),
            $this->getRequestHeaders(),
            json_encode(new Errors($this->config, $store))
        );

        $response = $this->client->send($request);
        return ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    /**
     * Get the Endpoint URI of the APM Server
     *
     * @param string $endpoint
     *
     * @return string
     */
    private function getEndpoint($endpoint)
    {
        return sprintf(
            '%s/%s/%s',
            $this->config->get('serverUrl'),
            $this->config->get('apmVersion'),
            $endpoint
        );
    }

    /**
     * Get the Headers for the POST Request
     *
     * @return array
     */
    private function getRequestHeaders()
    {
        // Default Headers Set
        $headers = array(
            'Content-Type' => 'application/json',
            'User-Agent'   => sprintf('elasticapm-php/%s', Agent::VERSION),
        );

        // Add Secret Token to Header
        if ($this->config->get('secretToken') !== null) {
            $headers['Authorization'] = sprintf('Bearer %s', $this->config->get('secretToken'));
        }

        return $headers;
    }
}
