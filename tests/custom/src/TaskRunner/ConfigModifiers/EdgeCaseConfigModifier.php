<?php

namespace My\Custom\TaskRunner\ConfigModifiers;

use Consolidation\Config\ConfigInterface;
use OpenEuropa\TaskRunner\Contract\ConfigModifierInterface;
use Robo\Robo;

/**
 * We set here a very low priority to run even after the default modifiers. This
 * is here just as proof that the default modifiers can be also overridden.
 *
 * @priority -2000
 */
class EdgeCaseConfigModifier implements ConfigModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public static function modify(ConfigInterface $config)
    {
        $config->combine(['whatever' => 'overwritten']);
    }
}
