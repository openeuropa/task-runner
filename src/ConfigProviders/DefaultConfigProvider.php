<?php

namespace OpenEuropa\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;

class DefaultConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(array &$config)
    {
        static::importFromFiles($config, [
            __DIR__.'/../../config/runner.yml',
            'runner.yml.dist',
        ]);
    }
}
