<?php

namespace My\Custom\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;

/**
 * @priority 1
 */
class TestConfigProvider implements ConfigProviderInterface
{
    use ConfigFromFilesTrait;

    /**
     * {@inheritdoc}
     */
    public static function provide(array &$config)
    {
        // This value is overridden with values from userconfig.yml.
        $config['whatever']['root'] = 'drupal';
        // Interleave third_party.yml between runner.yml.dist and runner.yml.
        static::importFromFiles($config, [
            __DIR__ . '/../../../../fixtures/third_party.yml',
        ]);
    }
}
