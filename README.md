# OpenEuropa Task Runner

[![Build Status](https://travis-ci.org/ec-europa/oe-task-runner.svg?branch=master)](https://travis-ci.org/ec-europa/oe-task-runner)

PHP task runner based on [Robo](http://robo.li), focused on extensibility. 

Quick references:

- [Installation](#installation)
- [Configuration](#configuration)
- [Built-in commands](#built-in-commands)
- [Expose custom commands as YAML configuration](#expose-custom-commands-via-yaml-configuration)
- [Expose custom commands as PHP classes](#expose-custom-commands-as-php-classes)

## Installation

Install it with Composer:

```
$ composer ec-europa/oe-task-runner
```

After installation run `./vendor/bin/run` to a list of available commands. 

## Configuration

The Task Runner command execution can be controlled in two ways:

1. By setting arguments and options when running a command.
2. By customizing default values via a `runner.yml.dist` in the current directory, which can be selectively overridden by providing an extra `runner.yml` 

A list of default values, with a brief explanation, can be found at [./config/runner.yml](./config/runner.yml).

## Built-in commands

The Task Runner comes with the following built-in commands:

| Command                      | Description |
| ---------------------------- |-------------|
| `changelog:generate`         | Generate a changelog based on GitHub issues and pull requests. |
| `drupal:site-install`        | Install a target Drupal site using default configuration values. |
| `drupal:site-pre-install`    | Run Drupal pre-install commands as listed under the `drupal.pre_install` property. |
| `drupal:site-post-install`   | Run Drupal post-install commands as listed under the `drupal.post_install` property. | 
| `drupal:settings-setup`      | Setup default Drupal settings file by appending settings specified at `drupal.settings` to the current site's `default.settings.php`. |
| `drupal:drush-setup`         | Write Drush 8 and 9 configuration files to specific directories. |

Run `./vendor/bin/run help [command-name]` for more information about each command's capabilities.

## Expose custom commands as YAML configuration

The Task Runner allows you to expose new commands by just listing its [tasks](http://robo.li/getting-started/#tasks)
under the `commands:` property in `robo.yml.dist`/`robo.yml`.

For example, the following YAML portion will expose two commands, `drupal:site-setup` and `setup:behat`:

```yaml
commands:
  drupal:site-setup:
    - { task: "chmod", file: "${drupal.root}/sites", permissions: 0774, recursive: true }
    - { task: "symlink", from: "../../custom/modules", to: "${drupal.root}/modules/custom" }
    - { task: "symlink", from: "../../custom/themes", to: "${drupal.root}/themes/custom" }
    - { task: "run", command: "drupal:drush-setup" }
    - { task: "run", command: "drupal:settings-setup" }
    - { task: "run", command: "setup:behat" }
  setup:behat:
    - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
```

Commands can reference each-other, allowing for complex scenarios to be implemented with relative ease.

At the moment the following tasks are supported (optional argument default values in parenthesis):

| Task      | Task                      | Arguments |
| --------- | ------------------------- | --------- |
| `mkdir`   | `taskFilesystemStack()`   | `dir`, `mode` (0777) |
| `touch`   | `taskFilesystemStack()`   | `file`, `time` (current time), `atime` (current time) |
| `copy`    | `taskFilesystemStack()`   | `from`, `to`, `force` (false) |
| `chmod`   | `taskFilesystemStack()`   | `file`, `permissions`, `umask` (0000), `recursive` (false) |
| `chgrp`   | `taskFilesystemStack()`   | `file`, `group`, `recursive` (false) |
| `chown`   | `taskFilesystemStack()`   | `file`, `user`, `recursive` (false) |
| `remove`  | `taskFilesystemStack()`   | `file` |
| `rename`  | `taskFilesystemStack()`   | `from`, `to`, `force` (false) |
| `symlink` | `taskFilesystemStack()`   | `from`, `to`, `copyOnWindows` (false) |
| `mirror`  | `taskFilesystemStack()`   | `from`, `to` |
| `process` | `taskProcessConfigFile()` | `from`, `to` |
| `run`     | `taskExec()`              | `command` (will run `./vendor/bin/run [command]`) |

## Expose custom commands as PHP classes

More complex commands can be provided by creating Task Runner command classes within your project's PSR-4 namespace.

For example, given you have the following PSR-4 namespace configured in our `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "My\\Project\\": "./src/"
        }
    }
}
```

Then you can expose extra commands by creating one or more classes under `./src/TaskRunner/Commands` which extend
`EC\OpenEuropa\TaskRunner\Commands\AbstractCommands`, as shown in the example below:

```php
<?php

namespace My\Project\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Class MyCustomCommands
 *
 * @package My\Project\TaskRunner\Commands
 */
class MyCustomCommands extends AbstractCommands
{
    /**
     * @command my-project:command-one
     */
    public function commandOne() { }

    /**
     * @command my-project:command-two
     */
    public function commandTwo() { }
}
```

After doing that remember to refresh your local autoloader by running `composer dump-autoload`.
 
You can now access your new commands via the Task Runner main executable:

```
$ ./vendor/bin/run 
OpenEuropa Task Runner 

Available commands:
 ...
 my-project
  my-project:command-four       
  my-project:command-one        
```

It is mandatory to place your command classes under `./src/TaskRunner/Commands`, otherwise the Task Runner will not
register them at startup.

Even if not mandatory it is recommended to extend `EC\OpenEuropa\TaskRunner\Commands\AbstractCommands`.

For a more details of how to expose custom commands please refer to [Robo documentation](http://robo.li/getting-started).
