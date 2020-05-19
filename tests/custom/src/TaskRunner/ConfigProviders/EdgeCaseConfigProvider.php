<?php

declare(strict_types=1);

namespace My\Custom\TaskRunner\ConfigProviders;

use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use Robo\Config\Config;

/**
 * We set here a very low priority to run even after the default providers. This
 * is here just as proof that the default providers can be also overridden.
 *
 * @priority -2000
 */
class EdgeCaseConfigProvider implements ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function provide(Config $config): void
    {
        $config->set('whatever', 'overwritten');
    }
}
