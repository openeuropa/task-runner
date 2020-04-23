<?php

namespace OpenEuropa\TaskRunner\Traits;

use Consolidation\Config\Loader\YamlConfigLoader;
use Dflydev\DotAccessData\Util;

trait ConfigFromFilesTrait
{
    /**
     * Loads configs as arrays from $files and merge them in $config.
     *
     * @param array $config
     * @param array $files
     */
    private static function importFromFiles(array &$config, array $files)
    {
        $loader = new YamlConfigLoader();
        foreach ($files as $file) {
            $config = Util::mergeAssocArray($config, $loader->load($file)->export());
        }
    }
}
