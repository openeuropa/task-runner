<?php

namespace OpenEuropa\TaskRunner\Contract;

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
     * Implementations should alter the `$config` array, passed by reference, by
     * adding, overriding or removing array elements. Variables are allowed.
     *
     * @param array $config
     */
    public static function provide(array &$config);
}
