# PHP Transmission-RPC API SDK

[![Join PHP Chat][ico-phpchat]][link-phpchat]
[![Chat on Telegram][ico-telegram]][link-telegram]
[![Laravel Package][ico-laravel]][link-repo]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

> A Transmission-RPC API SDK for PHP with Laravel Support.

## Install

Via Composer

``` bash
$ composer require irazasyed/php-transmission-sdk php-http/guzzle6-adapter
```

> **Note:** You can use HTTP Client of your choice, for the list of adapters please check [HTTPlug](http://httplug.io/).

### Laravel

> This package supports the [package discovery](https://laravel.com/docs/5.5/packages#package-discovery) functionality provided in Laravel >= 5.5, so you don't have to manually register the service provider or facade.

### Configuration - (Optional)

Copy the config file into your project

``` bash
php artisan vendor:publish --provider="Transmission\Laravel\ServiceProvider"
```

## Usage

``` php
$transmission = new Transmission\Client($hostname, $port, $username, $password, $httpClientBuilder = null);
$transmission->get(); // Get All Torrents.

// Laravel
$transfers = Transmission::get('recently-active'); //
```

> The SDK supports all the methods listed in specs. For more details, check out [transmission-rpc specs](https://git.io/transmission-rpc-specs).

[![Transmission-RPC API SDK Usage][sdk-usage]][link-repo]

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email gh@lukonet.com instead of using the issue tracker.

## Credits

- [Syed][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-phpchat]: https://img.shields.io/badge/Slack-PHP%20Chat-5c6aaa.svg?style=flat-square&logo=slack&labelColor=4A154B
[ico-telegram]: https://img.shields.io/badge/@PHPChatCo-2CA5E0.svg?style=flat-square&logo=telegram&label=Telegram
[ico-laravel]: https://img.shields.io/badge/Laravel-5~8-FF2D20.svg?style=flat-square&logo=laravel&labelColor=black&logoColor=white
[ico-version]: https://img.shields.io/packagist/v/irazasyed/php-transmission-sdk.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/irazasyed/php-transmission-sdk/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/irazasyed/php-transmission-sdk.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/irazasyed/php-transmission-sdk.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/irazasyed/php-transmission-sdk.svg?style=flat-square

[sdk-usage]: https://user-images.githubusercontent.com/1915268/43494955-0c47765e-9553-11e8-851e-14139d8bcd0d.png

[link-phpchat]: https://phpchat.co/?ref=php-transmission-sdk
[link-telegram]: https://t.me/PHPChatCo
[link-repo]: https://github.com/irazasyed/php-transmission-sdk
[link-packagist]: https://packagist.org/packages/irazasyed/php-transmission-sdk
[link-travis]: https://travis-ci.org/irazasyed/php-transmission-sdk
[link-scrutinizer]: https://scrutinizer-ci.com/g/irazasyed/php-transmission-sdk/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/irazasyed/php-transmission-sdk
[link-downloads]: https://packagist.org/packages/irazasyed/php-transmission-sdk
[link-author]: https://github.com/irazasyed
[link-contributors]: ../../contributors
