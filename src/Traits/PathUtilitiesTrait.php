<?php

namespace EC\OpenEuropa\TaskRunner\Traits;

/**
 * Trait PathUtilitiesTrait
 *
 * @package EC\OpenEuropa\TaskRunner\Traits
 */
trait PathUtilitiesTrait
{
    /**
     * Generate relative path from a location to another.
     *
     * Both paths are considered to be relative to the same root.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    protected function walkPath($source, $destination)
    {
        $steps = count(explode('/', $source)) - 1;
        $prefix = implode(array_fill(1, $steps, '..'), '/');

        return in_array($destination, ['.', './']) ? $prefix : $prefix.'/'.$destination;
    }
}
