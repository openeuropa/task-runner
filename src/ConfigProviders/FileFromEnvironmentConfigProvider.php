<?php

namespace OpenEuropa\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;

class FileFromEnvironmentConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(array &$config)
    {
        if ($yamlFile = static::getLocalConfigurationFilepath()) {
            static::importFromFiles($config, [$yamlFile]);
        }
    }

    /**
     * Gets the configuration filepath from environment variables.
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
