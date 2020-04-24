<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Contract;

use Robo\Config\Config;

/**
 * Interface for configuration providers.
 *
 * Classes implementing this interface:
 * - Should have `TaskRunner\ConfigProviders` as relative namespace. For
 *   instance when `Some\Namespace` points to the `src/` directory, then the
 *   class should be placed in `src/TaskRunner/ConfigProviders` and will have
 *   `Some\Namespace\TaskRunner\ConfigProviders` as namespace.
 * - The class name should end with the `ConfigProvider` suffix.
 *
 * @package OpenEuropa\TaskRunner\Contract
 */
interface ConfigProviderInterface
{
    /**
     * Adds or overrides configuration.
     *
     * Implementations should alter the `$config` object, passed to the method.
     * A convenient way to provide additional config or override the existing
     * one is to use the `ConfigFromFilesTrait::importFromFiles()` method and
     * load overrides form custom config .yml files. But the $config object can
     * be manipulated also directly using its methods, e.g. $config->().
     *
     * @param \Robo\Config\Config $config
     */
    public static function provide(Config $config): void;
}
