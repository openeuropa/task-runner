<?php

declare(strict_types=1);

namespace My\Custom\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
use Robo\Config\Config;

/**
 * @priority 1
 */
class TestConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(Config $config): void
    {
        // This value is overridden with values from userconfig.yml.
        $config->set('whatever.root', 'drupal');
        // Interleave third_party.yml between runner.yml.dist and runner.yml.
        static::importFromFiles($config, [
            __DIR__ . '/../../../../fixtures/third_party.yml',
        ]);
    }
}
