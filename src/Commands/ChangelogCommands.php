<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class ChangelogCommands
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class ChangelogCommands extends BaseCommands
{
    /**
     * @command changelog:generate
     *
     * @param array $options
     *
     * @return \Robo\Task\Docker\Run
     */
    public function generateChangelog(array $options = [
      'token' => InputOption::VALUE_REQUIRED,
      'tag' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $projectName = $this->getFullProjectName();
        $image = "muccg/github-changelog-generator {$projectName} -t {$options['token']}";
        if (!empty($options['tag'])) {
            $image .= " --future-release={$options['tag']}";
        }

        $task = $this->taskDockerRun($image)
          ->option('rm')
          ->rawArg('-v $(pwd):$(pwd)')
          ->rawArg('-w $(pwd)');

        return $task;
    }

    /**
     * Get project name from composer.json.
     *
     * @return string
     *   Project name.
     */
    protected function getFullProjectName()
    {
        $package = json_decode(file_get_contents('./composer.json'));

        return $package->name;
    }
}
