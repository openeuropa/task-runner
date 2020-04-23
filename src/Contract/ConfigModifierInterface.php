<?php

namespace OpenEuropa\TaskRunner\Contract;

use \Consolidation\Config\ConfigInterface;

/**
 * Provides an interface allowing alterations of task runner configuration.
 *
 * Classes implementing this interface:
 * - Should have `TaskRunner\ConfigModifiers` as relative namespace. For
 *   instance when `Some\Namespace` points to the `src/` directory, then the
 *   class should be placed in `src/TaskRunner/ConfigModifiers` and will have
 *   `Some\Namespace\TaskRunner\ConfigModifiers` as namespace.
 * - The class name should end with the `ConfigModifier` suffix.
 *
 * @package OpenEuropa\TaskRunner\Contract
 */
interface ConfigModifierInterface
{
    /**
     * Allows configuration alteration.
     *
     * @param ConfigInterface $config
     */
    public static function modify(ConfigInterface $config);
}
