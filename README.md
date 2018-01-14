# OpenEuropa Task Runner

[![Build Status](https://travis-ci.org/ec-europa/oe-task-runner.svg?branch=master)](https://travis-ci.org/ec-europa/oe-task-runner)

PHP task runner based on [Robo](http://robo.li/) and focused on extensibility. 

## Installation

Install it with Composer:

```
$ composer ec-europa/oe-task-runner
```

After installation run `./vendor/bin/run` to a list of available commands. 

## Configuration

Task Runner command execution can be controlled in two ways:

  1. By setting arguments and options when running a command (run `./vendor/bin/run help [command-name]` for more information it).
  2. By customizing default values in a local `runner.yml.dist`, which can be selectively overridden by providing an extra `runner.yml`. 

The list of default values, which a brief explanation, can be found at the [./config/runner.yml](./config/runner.yml).

