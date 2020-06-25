<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command for generating a changelog.
 */
class ChangelogCommands extends AbstractCommands implements ComposerAwareInterface
{
    use ComposerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../config/commands/changelog.yml';
    }

    /**
     * Generate a changelog based on GitHub issues and pull requests.
     *
     * Docker is required to run this command.
     * For more information check https://github.com/skywinder/github-changelog-generator
     *
     * @command changelog:generate
     *
     * @option token GitHub personal access token, to generate one visit https://github.com/settings/tokens/new
     * @option tag   Upcoming tag you wish to generate a new changelog entry for.
     *
     * @aliases changelog:g,cg
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
        $projectName = $this->getComposer()->getName();
        $exec = "{$projectName} -t {$options['token']}";
        if (!empty($options['tag'])) {
            $exec .= " --future-release={$options['tag']}";
        }

        $task = $this->taskDockerRun('muccg/github-changelog-generator')
            ->option('rm')
            ->rawArg('-v $(pwd):$(pwd)')
            ->rawArg('-w $(pwd)')
            ->exec($exec);

        return $task;
    }
}
