<?php

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigUtility;

use Consolidation\Config\Config;
use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Loader\ConfigProcessor;
use Robo\Config\Config as RoboConfig;

/**
 * Config that updates references when unprocessed config is updated.
 */
final class SelfProcessingRoboConfig extends RoboConfig
{

    const VARIABLES_SUBSTITUTED_CONTEXT = 'variables-substituted';

    /**
     * @var bool
     */
    protected $needsVariablesSubstitutedContextUpdate = FALSE;

    public function __construct(array $data = NULL) {
        parent::__construct($data);
        $this->ensureVariablesSubstitutedContext();
    }

    protected function ensureVariablesSubstitutedContext() {
        // Ensure our context is last.
        // @todo Replace with array_keys_last() once we require php 7.3.
        $lastContext = array_keys($this->contexts)[count($this->contexts) - 1];
        if ($lastContext !== self::VARIABLES_SUBSTITUTED_CONTEXT) {
            $config = $this->contexts[self::VARIABLES_SUBSTITUTED_CONTEXT] ?? new Config();
            unset($this->contexts[self::VARIABLES_SUBSTITUTED_CONTEXT]);
            $this->contexts[self::VARIABLES_SUBSTITUTED_CONTEXT] = $config;
        };
        if ($this->needsVariablesSubstitutedContextUpdate) {
            $this->updateVariablesSubstitutedContext();
        }
    }

    protected function updateVariablesSubstitutedContext() {
        // Resolve variables and import into that context.
        $processor = (new ConfigProcessor())->add($this->export());
        $this->getContext(self::VARIABLES_SUBSTITUTED_CONTEXT)
            ->replace($processor->export());
        $this->needsVariablesSubstitutedContextUpdate = FALSE;
    }

    protected function invalidateVariablesSubstitutedContext() {
        $this->needsVariablesSubstitutedContextUpdate = TRUE;
    }


    public function export() {
        // Export without variables-substituted-context.
        // @see \OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait::importFromFiles
        $contexts = $this->contexts;
        unset($contexts[self::VARIABLES_SUBSTITUTED_CONTEXT]);

        $export = [];
        foreach ($contexts as $name => $config) {
            $exportToMerge = $config->export();
            $export = \array_replace_recursive($export, $exportToMerge);
        }
        return $export;
    }


    public function import($data) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::import($data);
    }

    public function replace($data) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::replace($data);
    }

    public function combine($data) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::combine($data);
    }

    public function setSimulated($simulated = TRUE) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::setSimulated($simulated);
    }

    public function setInteractive($interactive = TRUE) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::setInteractive($interactive);
    }

    public function setDecorated($decorated = TRUE) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::setDecorated($decorated);
    }

    public function setProgressBarAutoDisplayInterval($interval) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::setProgressBarAutoDisplayInterval($interval);
    }

    public function set($key, $value) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::set($key, $value);
    }


    public function setDefault($key, $value) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::setDefault($key, $value);
    }

    public function addContext($name, ConfigInterface $config) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::addContext($name, $config);
    }

    public function increasePriority($name) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::increasePriority($name);
    }

    public function addPlaceholder($name) {
        $this->invalidateVariablesSubstitutedContext();
        return parent::addPlaceholder($name);
    }

    public function removeContext($name) {
        $this->invalidateVariablesSubstitutedContext();
        parent::removeContext($name);
    }



    public function isSimulated() {
        $this->ensureVariablesSubstitutedContext();
        return parent::isSimulated();
    }

    public function isInteractive() {
        $this->ensureVariablesSubstitutedContext();
        return parent::isInteractive();
    }

    public function isDecorated() {
        $this->ensureVariablesSubstitutedContext();
        return parent::isDecorated();
    }

    public function has($key) {
        $this->ensureVariablesSubstitutedContext();
        return parent::has($key);
    }

    public function get($key, $default = NULL) {
        $this->ensureVariablesSubstitutedContext();
        return parent::get($key, $default);
    }

    public function getSingle($key, $default = NULL) {
        $this->ensureVariablesSubstitutedContext();
        return parent::getSingle($key, $default);
    }

    public function getUnion($key) {
        $this->ensureVariablesSubstitutedContext();
        return parent::getUnion($key);
    }

    public function exportAll() {
        $this->ensureVariablesSubstitutedContext();
        return parent::exportAll();
    }

}
