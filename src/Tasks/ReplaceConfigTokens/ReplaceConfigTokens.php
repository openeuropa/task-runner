<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens;

use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Task\BaseTask;
use Robo\Task\File\Replace;
use Robo\Task\Filesystem\FilesystemStack;

/**
 * Class ReplaceConfigTokens
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens
 */
class ReplaceConfigTokens extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;

    const TOKEN_REGEX = '/\$\{(([A-Za-z_\-]+\.?)+)\}/';

    /**
     * Source file.
     *
     * @var string
     */
    protected $source;

    /**
     * Destination file.
     *
     * @var string
     */
    protected $destination;

    /**
     * @var \Robo\Task\Filesystem\FilesystemStack
     */
    protected $filesystem;

    /**
     * @var \Robo\Task\File\Replace
     */
    protected $replace;

    /**
     * ReplaceConfigTokens constructor.
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->filesystem = new FilesystemStack();
        $this->replace = new Replace($destination);
    }

    /**
     * @return \Robo\Result
     */
    public function run()
    {
        $content = file_get_contents($this->source);
        $config = $this->getConfig();

        $tokens = array_map(function ($key) use ($config) {
            return $config->get($key);
        }, $this->extractTokens($content));

        return $this->collectionBuilder()->addTaskList([
            $this->filesystem->copy($this->source, $this->destination, true),
            $this->replace->from(array_keys($tokens))->to(array_values($tokens)),
        ])->run();
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
