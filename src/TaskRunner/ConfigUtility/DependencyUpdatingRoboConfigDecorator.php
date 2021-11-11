<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\ConfigInterface;

/**
 * Wraps config and triggers processing after any setter call.
 */
final class DependencyUpdatingRoboConfigDecorator extends RoboConfigDecoratorBase
{

    /**
     * @var \OpenEuropa\TaskRunner\TaskRunner\ConfigUtility\DependencyUpdaterInterface
     */
    protected $dependencyUpdater;

    /**
     * @param \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface $config
     * @param \OpenEuropa\TaskRunner\TaskRunner\ConfigUtility\DependencyUpdaterInterface $dependencyUpdater
     */
    public function __construct($config, DependencyUpdaterInterface $dependencyUpdater) {
        parent::__construct($config);
        $this->dependencyUpdater = $dependencyUpdater;
    }

    public function addPlaceholder($name) {
        $result = parent::addPlaceholder($name);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function increasePriority($name) {
        $result = parent::increasePriority($name);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function removeContext($name) {
        parent::removeContext($name);
        $this->dependencyUpdater->updateDependencies();
    }

    public function import($data) {
        $result = parent::import($data);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function replace($data) {
        $result = parent::replace($data);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function combine($data) {
        $result = parent::combine($data);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function setSimulated($simulated = TRUE) {
        $result = parent::setSimulated($simulated);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function setInteractive($interactive = TRUE) {
        $result = parent::setInteractive($interactive);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function setDecorated($decorated = TRUE) {
        $result = parent::setDecorated($decorated);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function setProgressBarAutoDisplayInterval($interval) {
        $result = parent::setProgressBarAutoDisplayInterval($interval);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function addContext($name, ConfigInterface $config) {
        $result = parent::addContext($name, $config);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function set($key, $value) {
        $result = parent::set($key, $value);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

    public function setDefault($key, $value) {
        $result = parent::setDefault($key, $value);
        $this->dependencyUpdater->updateDependencies();
        return $result;
    }

}
