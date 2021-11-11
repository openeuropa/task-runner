<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Util\ConfigInterpolatorInterface;
use Consolidation\Config\Util\ConfigRuntimeInterface;

/**
 * @internal
 */
final class RoboConfigDecoratorBase implements ConfigInterface, ConfigInterpolatorInterface, ConfigRuntimeInterface
{

    /**
     * @var \Consolidation\Config\ConfigInterface|\Consolidation\Config\Util\ConfigInterpolatorInterface|\Consolidation\Config\Util\ConfigRuntimeInterface
     */
    protected $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function import($data) {
        return $this->config->import($data);
    }

    public function replace($data) {
        return $this->config->replace($data);
    }

    public function combine($data) {
        return $this->config->combine($data);
    }

    public function getGlobalOptionDefaultValues() {
        return $this->config->getGlobalOptionDefaultValues();
    }

    public function isSimulated() {
        return $this->config->isSimulated();
    }

    public function setSimulated($simulated = TRUE) {
        return $this->config->setSimulated($simulated);
    }

    public function isInteractive() {
        return $this->config->isInteractive();
    }

    public function setInteractive($interactive = TRUE) {
        return $this->config->setInteractive($interactive);
    }

    public function isDecorated() {
        return $this->config->isDecorated();
    }

    public function setDecorated($decorated = TRUE) {
        return $this->config->setDecorated($decorated);
    }

    public function setProgressBarAutoDisplayInterval($interval) {
        return $this->config->setProgressBarAutoDisplayInterval($interval);
    }

    public function interpolate($message, $default = '') {
        return $this->config->interpolate($message, $default);
    }

    public function mustInterpolate($message) {
        return $this->config->mustInterpolate($message);
    }

    public function addContext($name, ConfigInterface $config) {
        return $this->config->addContext($name, $config);
    }

    public function addPlaceholder($name) {
        return $this->config->addPlaceholder($name);
    }

    public function increasePriority($name) {
        return $this->config->increasePriority($name);
    }

    public function hasContext($name) {
        return $this->config->hasContext($name);
    }

    public function getContext($name) {
        return $this->config->getContext($name);
    }

    public function runtimeConfig() {
        return $this->config->runtimeConfig();
    }

    public function removeContext($name) {
        $this->config->removeContext($name);
    }

    public function findContext($key) {
        return $this->config->findContext($key);
    }

    public function has($key) {
        return $this->config->has($key);
    }

    public function get($key, $default = NULL) {
        return $this->config->get($key, $default);
    }

    public function getSingle($key, $default = NULL) {
        return $this->config->getSingle($key, $default);
    }

    public function getUnion($key) {
        return $this->config->getUnion($key);
    }

    public function set($key, $value) {
        $this->config->set($key, $value);
        return $this;
    }

    public function export() {
        return $this->config->export();
    }

    public function exportAll() {
        return $this->config->exportAll();
    }

    public function hasDefault($key) {
        return $this->config->hasDefault($key);
    }

    public function getDefault($key, $default = NULL) {
        return $this->config->getDefault($key, $default);
    }

    public function setDefault($key, $value) {
        return $this->config->setDefault($key, $value);
    }

}
