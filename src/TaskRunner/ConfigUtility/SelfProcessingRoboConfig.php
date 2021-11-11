<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\Loader\ConfigProcessor;
use Robo\Config\Config;

/**
 * Config that updates references when unprocessed config is updated.
 */
final class SelfProcessingRoboConfig extends Config implements SelfProcessingRoboConfigInterface
{

    /**
     * @var \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface
     */
    protected $unprocessedConfig;

    /**
     * @param \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface $unprocessedConfig
     */
    public function __construct($unprocessedConfig) {
        parent::__construct();
        $this->unprocessedConfig = $unprocessedConfig;
        $this->updateDependencies();
    }

    /**
     * @return \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface
     */
    public function getUnprocessedConfig() {
        return new DependencyUpdatingRoboConfigDecorator($this->unprocessedConfig, $this);
    }

    public function updateDependencies() {
        // Resolve variables and import into config.
        $processor = (new ConfigProcessor())->add($this->unprocessedConfig->export());
        $this->import($processor->export());
    }

    public function set($key, $value) {
        // Delegate to unprocessed config, and trigger update.
        $this->unprocessedConfig->set($key, $value);
        $this->updateDependencies();
        return $this;
    }


}
