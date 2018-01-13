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
  changelog:generate        Generate a changelog based on GitHub issues and pull requests.
 drupal
  drupal:drush-setup        Write Drush configuration files to the specified directory.
  drupal:settings-setup     Write Drupal site configuration files to the specified directory.
  drupal:site-install       Install target site.
  drupal:site-post-install  Run Drupal post-install commands.
  drupal:site-pre-install   Run Drupal pre-install commands.
 setup
  setup:behat
  setup:phpunit
```
