<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner;

use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use ScssPhp\ScssPhp\Compiler;

/**
 * Hides optional commands if the packages they depend on are not installed.
 *
 * Certain commands can only be used when an optional Composer package is
 * installed. This alters the command info by adding the '@hidden' annotation to
 * commands for which a dependency is missing.
 */
class CommandInfoAlterer implements \Consolidation\AnnotatedCommand\CommandInfoAltererInterface
{
    /**
     * List of optional commands mapped to the class they depend on.
     */
    const OPTIONAL_COMMAND_DEPENDENCIES = [
        // The compile-scss command depends on the "scssphp/scssphp" package.
        'assets:compile-scss' => Compiler::class
    ];

    public function alterCommandInfo(CommandInfo $commandInfo, $commandFileInstance)
    {
        // Hide optional commands if a package they depend on is not installed.
        $name = $commandInfo->getName();
        if (array_key_exists($name, self::OPTIONAL_COMMAND_DEPENDENCIES)) {
            if (!class_exists(self::OPTIONAL_COMMAND_DEPENDENCIES[$name])) {
                $commandInfo->addAnnotation('hidden', true);
            }
        }
    }
}
