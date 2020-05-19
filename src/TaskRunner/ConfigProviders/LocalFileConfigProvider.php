<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
use Robo\Config\Config;

/**
 * Provides local configuration.
 *
 * Developers working on projects using the task runner can put a "runner.yml"
 * file in the root directory of the project and provide any local configuration
 * overrides in this file. The file is meant for personal use and should be
 * added to .gitignore so it will not accidentally be committed.
 *
 * The config provider has significant low priority in order to ensure that will
 * run after all the third-party providers, making possible to override them,
 * but will run before FileFromEnvironmentConfigProvider, ensuring that
 * environment variables have the last word.
 *
 * @priority -1000
 */
class LocalFileConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(Config $config): void
    {
        static::importFromFiles($config, ['runner.yml']);
    }
}
