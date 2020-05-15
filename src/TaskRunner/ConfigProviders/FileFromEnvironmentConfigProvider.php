<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
use Robo\Config\Config;

/**
 * Provides configuration for the local environment.
 *
 * This will look in the following locations, and will apply the configuration
 * of the first found file. Any subsequent matches will be ignored.
 *
 * 1. Location specified in the OPENEUROPA_TASKRUNNER_CONFIG environment variable.
 * 2. Location following the freedesktop.org specification:
 *    ${XDG_CONFIG_HOME}/openeuropa/taskrunner/runner.yml
 * 3. The configuration in the user's home folder:
 *    {$HOME}/.config/openeuropa/taskrunner/runner.yml
 *
 * The config provider priority is very low to make sure this config provider
 * runs at the very end, being able override configurations from all other
 * providers in the chain. However, in some very special circumstances,
 * third-party config providers are still abie to set priorities lower than
 * this, making possible to override even config provided by this plugin.
 *
 * @priority -1500
 */
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
     * @return string|null
     *   The local configuration file path, or null if it doesn't exist.
     */
    private static function getLocalConfigurationFilepath(): ?string
    {
        if ($config = getenv('OPENEUROPA_TASKRUNNER_CONFIG')) {
            return $config;
        }

        if ($config = getenv('XDG_CONFIG_HOME')) {
            return $config . '/' . static::DEFAULT_CONFIG_LOCATION;
        }

        if ($home = getenv('HOME')) {
            return getenv('HOME') . '/.config/' . static::DEFAULT_CONFIG_LOCATION;
        }

        return null;
    }
}
