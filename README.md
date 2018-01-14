# OpenEuropa Task Runner

[![Build Status](https://travis-ci.org/ec-europa/oe-task-runner.svg?branch=master)](https://travis-ci.org/ec-europa/oe-task-runner)

PHP task runner based on [Robo](http://robo.li/), focused on extensibility. 

Quick references:

- [Installation](#installation)
- [Configuration](#configuration)
- [Built-in commands](#built-in-commands)
- [Expose custom via PHP classes](#expose-custom-via-php-classes)
- [Expose commands via local `robo.yml`](#expose-commands-via-local-robo-yml)

## Installation

Install it with Composer:

```
$ composer ec-europa/oe-task-runner
```

After installation run `./vendor/bin/run` to a list of available commands. 

## Configuration

The Task Runner command execution can be controlled in two ways:

1. By setting arguments and options when running a command (run `./vendor/bin/run help [command-name]` for more
   information about each command's capabilities
2. By customizing default values via a `runner.yml.dist` in the current directory, which can be selectively overridden by providing an extra `runner.yml` 

A list of default values, with a brief explanation, can be found at [./config/runner.yml](./config/runner.yml).

## Built-in commands

The Task Runner comes with the following built-in commands:

- `changelog:generate`: Generate a changelog based on GitHub issues and pull requests.
- `drupal:site-install`: Install a target Drupal site using default configuration values.
- `drupal:site-pre-install`: Run Drupal pre-install commands as listed under the `drupal.pre_install` property.
- `drupal:site-post-install`: Run Drupal post-install commands as listed under the `drupal.post_install` property. 
- `drupal:settings-setup`: Setup default Drupal settings file by appending settings specified at `drupal.settings` to the current site's `default.settings.php`.
- `drupal:drush-setup`: Write Drush 8 and 9 configuration files to specific directories.

## Expose custom via PHP classes

## Expose commands via local `robo.yml`