<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Traits;

use Consolidation\Config\Loader\YamlConfigLoader;
use Dflydev\DotAccessData\Util;
use Robo\Config\Config;

/**
 * Reusable code for loading configuration stored in files.
 */
trait ConfigFromFilesTrait
{
    /**
     * Loads configuration from YAML files and merges it in the given config object.
     *
     * @param \Robo\Config\Config $config
     *   The configuration object to update.
     * @param array $files
     *   An array of paths to configuration files in YAML format.
     */
    private static function importFromFiles(Config $config, array $files)
    {
        $loader = new YamlConfigLoader();
        foreach ($files as $file) {
            $configArray = Util::mergeAssocArray($config->export(), $loader->load($file)->export());
            $config->replace($configArray);
        }
    }
}
