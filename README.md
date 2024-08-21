# simplesamlphp-module-authtwitter

![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-authtwitter/actions/workflows/php.yml/badge.svg)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-authtwitter/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-authtwitter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-authtwitter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-authtwitter/?branch=master)
[![Type Coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authtwitter/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authtwitter)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authtwitter/level.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authtwitter)

## Install

Install with composer

```bash
    vendor/bin/composer require simplesamlphp/simplesamlphp-module-authtwitter
```

## Configuration

Next thing you need to do is to enable the module: in `config.php`,
search for the `module.enable` key and set `authtwitter` to true:

```php
    'module.enable' => [
        'authtwitter' => true,
        …
    ],
```
