<?php

namespace OpenEuropa\TaskRunner\Traits;

use Consolidation\Config\Loader\YamlConfigLoader;
use Dflydev\DotAccessData\Util;
use Robo\Config\Config;

trait ConfigFromFilesTrait
{
    /**
     * Loads configs from $files and merge them in $config.
     *
     * @param \Robo\Config\Config $config
     * @param array $files
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
