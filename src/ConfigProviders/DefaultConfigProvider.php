<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
use Robo\Config\Config;

class DefaultConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(Config $config): void
    {
        static::importFromFiles($config, [
            __DIR__.'/../../config/runner.yml',
            'runner.yml.dist',
        ]);
    }
}
