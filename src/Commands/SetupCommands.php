<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Tasks\ReplaceConfigTokens as ReplaceConfigTokens;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SetupCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class SetupCommands extends BaseCommands
{
    use ReplaceConfigTokens\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__.'/../../config/commands/setup.yml';
    }

    /**
     * Setup Behat.
     *
     * This command will copy behat.yml.dist in behat.yml and replace
     * configuration tokens with values provided in runner.yml.dist/runner.yml.
     *
     * For example, given the following configuration:
     *
     * > drupal:
     * >   root: build
     *
     * Then its token format would be: ${drupal.root}
     *
     * @command setup:behat
     *
     * @option source      Source configuration file.
     * @option destination Destination configuration file.
     *
     * @aliases setup:b,sb
     *
     * @param array $options
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function setupBehat(array $options = [
      'source'      => InputOption::VALUE_REQUIRED,
      'destination' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->setupReplace($options);
    }

    /**
     * Setup PHPUnit.
     *
     * This command will copy phpunit.xml.dist in phpunit.xml and replace
     * configuration tokens with values provided in runner.yml.dist/runner.yml.
     *
     * For example, given the following configuration:
     *
     * > drupal:
     * >   root: build
     *
     * Then its token format would be: ${drupal.root}
     *
     * @command setup:phpunit
     *
     * @option source      Source configuration file.
     * @option destination Destination configuration file.
     *
     * @aliases setup:p,sp
     *
     * @param array $options
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function setupPhpunit(array $options = [
      'source'      => InputOption::VALUE_REQUIRED,
      'destination' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->setupReplace($options);
    }


    /**
     * Replace configuration tokens in a text file.
     *
     * This command will copy source file in destination file and replace
     * configuration tokens with values provided in runner.yml.dist/runner.yml.
     *
     * For example, given the following configuration:
     *
     * > drupal:
     * >   root: build
     *
     * Then its token format would be: ${drupal.root}
     *
     * @command setup:replace
     *
     * @option source      Source configuration file.
     * @option destination Destination configuration file.
     *
     * @aliases setup:p,sp
     *
     * @param array $options
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function setupReplace(array $options = [
      'source'      => InputOption::VALUE_REQUIRED,
      'destination' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->taskReplaceConfigTokens($options['source'], $options['destination']);
    }
}
