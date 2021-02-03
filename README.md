# Task Runner
[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/task-runner/status.svg)](https://drone.fpfis.eu/openeuropa/task-runner)
[![Packagist](https://img.shields.io/packagist/v/openeuropa/task-runner.svg)](https://packagist.org/packages/openeuropa/task-runner)

PHP task runner based on [Robo](http://robo.li), focused on extensibility.

Quick references:

- [Installation](#installation)
- [Configuration](#configuration)
- [Built-in commands](#built-in-commands)
- [Expose custom commands as YAML configuration](#expose-custom-commands-as-yaml-configuration)
- [Expose custom commands as PHP classes](#expose-custom-commands-as-php-classes)

## Installation

Install it with [Composer](https://getcomposer.org):

```bash
composer require openeuropa/task-runner
```

After installation run `./vendor/bin/run` for a list of available commands.

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools to get the tests running, regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec web composer install
```

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

#### Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to
the container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.

## Configuration

Task Runner commands can be customized in two ways:

1. By setting arguments and options when running a command.
2. By providing default values in configuration files. The task runner will read
   the following files in the specified order. Options supplied in later files
   will override earlier ones:
    * The defaults provided by Task Runner. This file is located inside the Task
      Runner repository in `config/runner.yml`.
    * `runner.yml.dist` - project specific defaults. This file should be placed
      in the root folder of the project that depends on the Task Runner. Use
      this file to declare default options which are expected to work with your
      application under regular circumstances. This file should be committed in
      the project.
    * Third parties might implement config providers to modify the config. A
      config provider is a class implementing the `ConfigProviderInterface`.
      Such a class should be placed under the `TaskRunner\ConfigProviders`
      relative namespace. For instance when `Some\Namespace` points to `src/`
      directory, then the config provider class should be placed under the
      `src/TaskRunner/ConfigProviders` directory and will have the namespace set
      to `Some\Namespace\TaskRunner\ConfigProviders`. The class name should end
      with the `ConfigProvider` suffix. Use the `::provide()` method to alter
      the configuration object. A `@priority` annotation tag can be defined in
      the class docblock in order to determine the order in which the config
      providers are running. If omitted, `@priority 0` is assumed. This
      mechanism allows also to insert custom YAML config files in the flow, see
      the following example:
      ```
      namespace Some\Namespace\TaskRunner\ConfigProviders;

      use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
      use OpenEuropa\TaskRunner\Traits\ConfigFromFilesTrait;
      use Robo\Config\Config;

      /**
       * @priority 100
       */
      class AddCustomFileConfigProvider implements ConfigProviderInterface
      {
          use ConfigFromFilesTrait;
          public static function provide(Config $config): void
          {
              // Load the configuration from custom.yml and custom2.yml and
              // apply it to the configuration object. This will override config
              // from runner.yml.dist (which has priority 1500) but get
              // overridden by the config from runner.yml (priority -1000).
              static::importFromFiles($config, [
                  'custom.yml',
                  'custom2.yml',
              ]);
          }
      }
      ```
    * `runner.yml` - project specific user overrides. This file is also located
      in the root folder of the project that depends on the Task Runner. This
      file can be used to override options with values that are specific to the
      user's local environment. It is considered good practice to add this file
      to `.gitignore` to prevent `runner.yml` from being accidentally committed
      in the project repository.
    * User provided global overrides stored in environment variables. These can
      be used to define environment specific configuration that applies to all
      projects that use the Task Runner, such as database credentials and the
      Github access token. The following locations will be checked and the first
      one that is found will be used:
        * `$OPENEUROPA_TASKRUNNER_CONFIG`
        * `$XDG_CONFIG_HOME/openeuropa/taskrunner/runner.yml`
        * `$HOME/.config/openeuropa/taskrunner/runner.yml`

- [Installation](#installation)

A list of default values, with a brief explanation, can be found at the default
[`runner.yml`](./config/runner.yml).


## Built-in commands

The Task Runner comes with the following built-in commands:

| Command                      | Description |
| ---------------------------- |-------------|
| `drupal:site-install`        | Install a target Drupal site using default configuration values and/or CLI options |
| `drupal:site-pre-install`    | Run Drupal pre-install commands as listed under the `drupal.pre_install` property |
| `drupal:site-post-install`   | Run Drupal post-install commands as listed under the `drupal.post_install` property |
| `drupal:settings-setup`      | Setup default Drupal settings file by appending values specified at `drupal.settings` |
| `drupal:drush-setup`         | Setup Drush 8 and 9 configuration files |
| `release:create-archive`     | Create and archive a release for the current project |

Run `./vendor/bin/run help [command-name]` for more information about each command's capabilities.

## Expose "dynamic" commands as YAML configuration

The Task Runner allows you to expose new commands by just listing its [tasks](http://robo.li/getting-started/#tasks)
under the `commands:` property in `runner.yml.dist`/`runner.yml`.

For example, the following YAML portion will expose two dynamic commands, `drupal:site-setup` and `setup:behat`:

```yaml
commands:
  drupal:site-setup:
    - { task: "chmod", file: "${drupal.root}/sites", permissions: 0774, recursive: true }
    - { task: "symlink", from: "../../custom/modules", to: "${drupal.root}/modules/custom" }
    - { task: "symlink", from: "../../custom/themes", to: "${drupal.root}/themes/custom" }
    - { task: "run", command: "drupal:drush-setup" }
    - { task: "run", command: "drupal:settings-setup" }
    - { task: "run", command: "setup:behat" }
    - "./vendor/bin/drush --root=$(pwd)/${drupal.root} cr"
  setup:behat:
    - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
```

Commands can reference each-other, allowing for complex scenarios to be implemented with relative ease.

At the moment the following tasks are supported (optional argument default values in parenthesis):

| Task          | Task                         | Arguments |
| ------------- | ---------------------------- | --------- |
| `mkdir`       | `taskFilesystemStack()`      | `dir`, `mode` (0777) |
| `touch`       | `taskFilesystemStack()`      | `file`, `time` (current time), `atime` (current time) |
| `copy`        | `taskFilesystemStack()`      | `from`, `to`, `force` (false) |
| `chmod`       | `taskFilesystemStack()`      | `file`, `permissions`, `umask` (0000), `recursive` (false) |
| `chgrp`       | `taskFilesystemStack()`      | `file`, `group`, `recursive` (false) |
| `chown`       | `taskFilesystemStack()`      | `file`, `user`, `recursive` (false) |
| `remove`      | `taskFilesystemStack()`      | `file` |
| `rename`      | `taskFilesystemStack()`      | `from`, `to`, `force` (false) |
| `symlink`     | `taskFilesystemStack()`      | `from`, `to`, `copyOnWindows` (false) |
| `mirror`      | `taskFilesystemStack()`      | `from`, `to` |
| `process`     | `taskProcessConfigFile()`    | `from`, `to` |
| `process-php` | `taskAppendConfiguration()`  | `type: append`, `config`, `source`, `destination`, `override` (false) |
| `process-php` | `taskPrependConfiguration()` | `type: prepend`, `config`, `source`, `destination`, `override` (false) |
| `process-php` | `taskWriteConfiguration()`   | `type: write`, `config`, `source`, `destination`, `override` (false) |
| `run`         | `taskExec()`                 | `command`, `arguments`, `options` (will run `./vendor/bin/run [command] [argument1] [argument2] ... --[option1]=[value1] --[option2]=[value2] ...`) |

Tasks provided as plain-text strings will be executed as is in the current working directory.

## Expose custom commands as PHP classes

More complex commands can be provided by creating Task Runner command classes within your project's PSR-4 namespace.

For example, given you have the following PSR-4 namespace in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "My\\Project\\": "./src/"
        }
    }
}
```

Then you can expose extra commands by creating one or more classes under `./src/TaskRunner/Commands`, as shown in the
example below:

```php
<?php

namespace My\Project\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

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

```bash
$ ./vendor/bin/run
OpenEuropa Task Runner

Available commands:
 ...
 my-project
  my-project:command-four
  my-project:command-one
```

**NOTE:** It is mandatory to place your command classes under `./src/TaskRunner/Commands`, otherwise the Task Runner will not
register them at startup.

Even if not mandatory it is recommended for your command classes to extend `OpenEuropa\TaskRunner\Commands\AbstractCommands`.

For more details on how to expose custom commands please refer to the main [Robo documentation](http://robo.li/getting-started).

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/task-runner/tags).

