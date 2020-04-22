<?php

namespace OpenEuropa\TaskRunner\Event;

use Robo\Config\Config;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ConfigEvent.
 *
 * @package OpenEuropa\TaskRunner\Event
 */
class ConfigEvent extends Event
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
