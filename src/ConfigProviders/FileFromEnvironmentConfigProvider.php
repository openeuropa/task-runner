<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
use Robo\Config\Config;

class FileFromEnvironmentConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(Config $config): void
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
    private static function getLocalConfigurationFilepath(string $configuration_file = 'openeuropa/taskrunner/runner.yml'): ?string
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
