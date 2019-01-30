<?php

namespace OpenEuropa\TaskRunner\Traits;

/**
 * Class ConfigurationTokensTrait
 *
 * @package OpenEuropa\TaskRunner\Traits
 */
trait ConfigurationTokensTrait
{
    /**
     * Extract token in given text.
     *
     * @param $text
     *
     * @return array
     */
    protected function extractRawTokens($text)
    {
        preg_match_all('/\$\{(([A-Za-z_\-]+\.?)+)\}/', $text, $matches);
        if (isset($matches[0]) && !empty($matches[0]) && is_array($matches[0])) {
            return array_combine($matches[0], $matches[1]);
        }

        return [];
    }

    /**
     * Extract tokens and replace their values with current configuration.
     *
     * @param $text
     *
     * @return array
     */
    protected function extractProcessedTokens($text)
    {
        /** @var \Robo\Config\Config $config */
        $config = $this->getConfig();

        return array_map(function ($key) use ($config) {
            $value = $config->get($key);
            if (is_array($value)) {
                return implode(',', $value);
            }
            return $value;
        }, $this->extractRawTokens($text));
    }
}
