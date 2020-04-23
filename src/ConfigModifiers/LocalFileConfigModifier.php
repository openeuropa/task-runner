<?php

namespace OpenEuropa\TaskRunner\ConfigModifiers;

use Consolidation\Config\ConfigInterface;
use OpenEuropa\TaskRunner\Contract\ConfigModifierInterface;
use Robo\Robo;

class LocalFileConfigModifier implements ConfigModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public static function modify(ConfigInterface $config)
    {
        Robo::loadConfiguration([
            'runner.yml',
        ], $config);
    }
}
