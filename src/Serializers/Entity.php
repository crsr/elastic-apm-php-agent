<?php

namespace PhilKra\Serializers;

use PhilKra\Agent;
use PhilKra\Helper\Config;

/**
 *
 * Base Class with Common Settings for the Serializers
 *
 */
class Entity
{
    /**
     * @var \PhilKra\Helper\Config
     */
    protected $config;

    /**
     * @param Config $config
     * @param Store  $transactions
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the shared Schema Skeleton
     *
     * @return array
     */
    protected function getSkeleton()
    {
        return array(
            'service' => array(
                'name'    => $this->config->get('appName'),
                'version' => $this->config->get('appVersion'),
                'framework' => array(
                    'name' => $this->config->get('framework') !== null ? $this->config->get('framework') : '',
                    'version' => $this->config->get('frameworkVersion') != null ? $this->config->get('frameworkVersion') : '',
                ),
                'language' => array(
                    'name'    => 'php',
                    'version' => phpversion()
                ),
                'process' => array(
                    'pid' => getmypid(),
                ),
                'agent' => array(
                    'name'    => Agent::NAME,
                    'version' => Agent::VERSION
                ),
                'environment' => $this->config->get('environment')
            ),
            'system' => array(
                'hostname'     => $this->config->get('hostname'),
                'architecture' => php_uname('m'),
                'platform'     => php_uname('s')
            )
        );
    }
}
