<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens;

use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;

/**
 * Class ReplaceConfigTokens
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens
 */
class ReplaceConfigTokens extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;

    const TOKEN_REGEX = '/\$\{((\w+\.?)+)\}/';

    /**
     * @return \Robo\Result
     */
    public function run()
    {
        return Result::success($this);
    }

    /**
     * @param $text
     *
     * @return array
     */
    protected function extractTokens($text)
    {
        preg_match_all(self::TOKEN_REGEX, $text, $matches);
        if (isset($matches[0]) && !empty($matches[0]) && is_array($matches[0])) {
            return array_combine($matches[0], $matches[1]);
        }

        return [];
    }
}
