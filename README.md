# OpenEuropa Task Runner

[![Build Status](https://travis-ci.org/ec-europa/oe-task-runner.svg?branch=master)](https://travis-ci.org/ec-europa/oe-task-runner)

PHP task runner based on Robo.

Install it with Composer:

```
$ composer ec-europa/oe-task-runner
```

After installation run `./vendor/bin/run` and check each command's help section:

```bash
$ ./vendor/bin/run
OpenEuropa Task Runner 

Usage:
  command [options] [arguments]

Options:
  -h, --help                           Display this help message
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi                           Force ANSI output
      --no-ansi                        Disable ANSI output
  -n, --no-interaction                 Do not ask any interactive question
      --simulate                       Run in simulated mode (show what would have happened).
      --progress-delay=PROGRESS-DELAY  Number of seconds before progress bar is displayed in long-running task collections. Default: 2s. [default: 2]
  -D, --define=DEFINE                  Define a configuration item value. (multiple values allowed)
      --working-dir=WORKING-DIR        Working directory, defaults to current working directory. [default: "."]
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help                      Displays help for a command
  list                      Lists commands
 changelog
  changelog:generate        [changelog:g|cg] Generate a changelog based on GitHub issues and pull requests.
 drupal
  drupal:component-setup    [drupal:component-scaffold|drupal:cs|dcs] Scaffold Drupal component development.
  drupal:site-install       [drupal:si|dsi] Install target site.
  drupal:site-post-install  [drupal:spi|dspi] Run Drupal post-install commands.
  drupal:site-setup         [drupal:site-scaffold|drupal:ss|dss] Setup local Drupal site development copy.
 setup
  setup:behat               [setup:b|sb] Setup Behat.
  setup:phpunit             [setup:p|sp] Setup PHPUnit.
  setup:replace             [setup:r|sr] Replace configuration tokens in a text file.
```
