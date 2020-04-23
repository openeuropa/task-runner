<?php

namespace OpenEuropa\TaskRunner\ConfigModifiers;

use Consolidation\Config\ConfigInterface;
use OpenEuropa\TaskRunner\Contract\ConfigModifierInterface;
use Robo\Robo;

class FileFromEnvironmentConfigModifier implements ConfigModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public static function modify(ConfigInterface $config)
    {
        if ($yamlFile = static::getLocalConfigurationFilepath()) {
            Robo::loadConfiguration([$yamlFile], $config);
        }
    }

    /**
     * Gets the configuration filepath from environment variable.
     *
     * @param string $configuration_file
     *   The default filepath.
     *
     * @return string|null
     *   The local configuration file path, or null if it doesn't exist.
     */
    private static function getLocalConfigurationFilepath($configuration_file = 'openeuropa/taskrunner/runner.yml')
    {
        if ($config = getenv('OPENEUROPA_TASKRUNNER_CONFIG')) {
            return $config;
        }

        if ($config = getenv('XDG_CONFIG_HOME')) {
            return $config . '/' . $configuration_file;
        }

        if ($home = getenv('HOME')) {
            return getenv('HOME') . '/.config/' . $configuration_file;
        }

        return null;
    }
}
