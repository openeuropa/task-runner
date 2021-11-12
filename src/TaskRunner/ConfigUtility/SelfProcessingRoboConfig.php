<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Loader\ConfigProcessor;
use Robo\Config\Config;

/**
 * Config that updates references when unprocessed config is updated.
 */
final class SelfProcessingRoboConfig extends Config
{

    /**
     * @var \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface|null
     */
    protected $processedConfig = NULL;

    /**
     * @return \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface|null
     */
    protected function getProcessedConfig() {
        if (!$this->processedConfig) {
            $this->updateProcessedConfig();
        }
        return $this->processedConfig;
    }

    protected function updateProcessedConfig() {
        // Resolve variables and import into config.
        $processor = (new ConfigProcessor())->add(parent::export());
        $this->processedConfig = new Config($processor->export());
    }

    protected function invalidateProcessedConfig() {
        $this->processedConfig = null;
    }



    public function import($data) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::import($data);
    }

    public function replace($data) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::replace($data);
    }

    public function combine($data) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::combine($data);
    }

    public function setSimulated($simulated = TRUE) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::setSimulated($simulated);
    }

    public function setInteractive($interactive = TRUE) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::setInteractive($interactive);
    }

    public function setDecorated($decorated = TRUE) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::setDecorated($decorated);
    }

    public function setProgressBarAutoDisplayInterval($interval) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::setProgressBarAutoDisplayInterval($interval);
    }

    public function set($key, $value) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::set($key, $value);
    }

    public function setDefault($key, $value) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::setDefault($key, $value);
    }


    public function addContext($name, ConfigInterface $config) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::addContext($name, $config);
    }

    public function increasePriority($name) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::increasePriority($name);
    }

    public function addPlaceholder($name) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        return parent::addPlaceholder($name);
    }

    public function removeContext($name) {
        // Setters act on $this and invalidate processed config.
        $this->invalidateProcessedConfig();
        parent::removeContext($name);
    }



    public function hasContext($name) {
        // ContextGetters act on $this, no need to invalidate.
        return parent::hasContext($name);
    }

    public function getContext($name) {
        // ContextGetters act on $this, no need to invalidate.
        return parent::getContext($name);
    }

    public function runtimeConfig() {
        // ContextGetters act on $this, no need to invalidate.
        return parent::runtimeConfig();
    }

    public function findContext($key) {
        // ContextGetters act on $this, no need to invalidate.
        return parent::findContext($key);
    }

    public function hasDefault($key) {
        // ContextGetters act on $this, no need to invalidate.
        return parent::hasDefault($key);
    }

    public function getDefault($key, $default = NULL) {
        // ContextGetters act on $this, no need to invalidate.
        return parent::getDefault($key, $default);
    }



    public function isSimulated() {
        // Getters act on processed config.
        return $this->getProcessedConfig()->isSimulated();
    }

    public function isInteractive() {
        // Getters act on processed config.
        return $this->getProcessedConfig()->isInteractive();
    }

    public function isDecorated() {
        // Getters act on processed config.
        return $this->getProcessedConfig()->isDecorated();
    }

    public function has($key) {
        // Getters act on processed config.
        return $this->getProcessedConfig()->has($key);
    }

    public function get($key, $default = NULL) {
        // Getters act on processed config.
        return $this->getProcessedConfig()->get($key, $default);
    }

    public function getSingle($key, $default = NULL) {
        // Getters act on processed config.
        return $this->getProcessedConfig()->getSingle($key, $default);
    }

    public function getUnion($key) {
        // Getters act on processed config.
        return $this->getProcessedConfig()->getUnion($key);
    }

    public function export() {
        // Getters act on processed config.
        return $this->getProcessedConfig()->export();
    }

    public function exportAll() {
        // Getters act on processed config.
        return $this->getProcessedConfig()->exportAll();
    }



    public function interpolate($message, $default = '') {
        // Getters act on processed config. Copied trait method.
        return $this->getInterpolator()->interpolate($this->getProcessedConfig(), $message, $default);
    }

    public function mustInterpolate($message) {
        // Getters act on processed config. Copied trait method.
        return $this->getInterpolator()->mustInterpolate($this->getProcessedConfig(), $message);
    }

}
