<?php

namespace My\Custom\TaskRunner\ConfigModifiers;

use Consolidation\Config\ConfigInterface;
use OpenEuropa\TaskRunner\Contract\ConfigModifierInterface;
use Robo\Robo;

/**
 * @priority 1
 */
class TestConfigModifier implements ConfigModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public static function modify(ConfigInterface $config)
    {
        // This value is overridden with values from userconfig.yml.
        $config->combine(['whatever' => ['root' => 'drupal']]);
        // Interleave third_party.yml between runner.yml.dist and runner.yml.
        Robo::loadConfiguration([
            __DIR__ . '/../../../../fixtures/third_party.yml',
        ], $config);
    }
}
