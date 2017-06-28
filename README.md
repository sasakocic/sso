# Integrations

Login Integrations like Facebook, Vkontakte, Nasza Klasa, ...

[![Build Status](https://img.shields.io/travis/facebook/php-graph-sdk/5.4.svg)](https://travis-ci.org/sasakocic/integrations)
[![Code Style Status](https://styleci.io/repos/83059149/shield)](https://styleci.io/repos/87685511)
[![CodeCov](https://img.shields.io/codecov/c/github/sasakocic/integrations.svg)](https://codecov.io/gh/sasakocic/integrations)
[![CodeClimate](https://img.shields.io/codeclimate/github/sasakocic/integrations.svg)](https://codeclimate.com/github/sasakocic/integrations)
[![Issue Count](https://codeclimate.com/github/sasakocic/integrations/badges/issue_count.svg)](https://codeclimate.com/github/sasakocic/integrations)
[![Issue Count](https://scrutinizer-ci.com/g/sasakocic/integrations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sasakocic/integrations/?branch=master)

## Installation

Installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer install
```

## Usage

Usage can be seen from `index.php` or [here](https://integrations.yahuah.net/naszaklasa)

```php
$params = [
    'redirectHost' => 'https://integrations.yahuah.net',
    'redirectUri' => '/naszaklasa/callback',
];
$service = NaszaklasaService::create($params);
// URL for the button
$url = $service->getLoginUrl();
// Get User data
$userData = $service->getUser($query);
```

## Tests

1. [Composer](https://getcomposer.org/) is a prerequisite for running the tests. Install composer globally, then run `composer install` to install required files.
2. The tests can be executed by running this command from the root directory:

```bash
$ ./vendor/bin/phpunit
```

## Documentation

- [Software Documentation](http://build.yahuah.net/integrations/docs/html/index.xhtml)
- [Code Coverage](http://build.yahuah.net/integrations/docs/coverage/index.html)
- [PDepend](http://build.yahuah.net/integrations/docs/pdepend-process/index.html)
- [PHP Code Browser](http://build.yahuah.net/integrations/docs/phpcb/)

![Dependencies](http://build.yahuah.net/integrations/pdepend/dependencies.svg)
![Overview Pyramid](http://build.yahuah.net/integrations/pdepend/overview-pyramid.svg)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

Please see the [license file](LICENSE) for more information.
