# This is my package lunar-api-stripe-adapter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dystcz/lunar-api-stripe-adapter.svg?style=flat-square)](https://packagist.org/packages/dystcz/lunar-api-stripe-adapter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dystcz/lunar-api-stripe-adapter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dystcz/lunar-api-stripe-adapter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dystcz/lunar-api-stripe-adapter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dystcz/lunar-api-stripe-adapter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dystcz/lunar-api-stripe-adapter.svg?style=flat-square)](https://packagist.org/packages/dystcz/lunar-api-stripe-adapter)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require dystcz/lunar-api-stripe-adapter
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="lunar-api-stripe-adapter-config"
```

This is the contents of the published config file:

```php
return [
    'driver' => 'stripe',
    'type' => 'card',
];
```

## Usage

```php
// Create a payment intent
App::make(StripePaymentAdapter::class)->createIntent($cart)

// Handle a webhook (validate and authorize payment)
App::make(StripePaymentAdapter::class)->handleWebhook($request)
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jakub Theimer](https://github.com/dystcz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
