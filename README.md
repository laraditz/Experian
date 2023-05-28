# Laravel Experian

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/experian.svg?style=flat-square)](https://packagist.org/packages/laraditz/experian)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/experian.svg?style=flat-square)](https://packagist.org/packages/laraditz/experian)
![GitHub Actions](https://github.com/laraditz/experian/actions/workflows/main.yml/badge.svg)

A simple laravel package for Experian B2B Web Service.

## Installation

You can install the package via composer:

```bash
composer require laraditz/experian
```

## Before Start

Configure your variables in your `.env` (recommended) or you can publish the config file and change it there.

```
EXPERIAN_VENDOR=<vendor>
EXPERIAN_USERNAME=<username>
EXPERIAN_PASSWORD=<password>
```

(Optional) You can publish the config file via this command:
```bash
php artisan vendor:publish --provider="Laraditz\Experian\ExperianServiceProvider" --tag="config"
```

Run the migration command to create the necessary database table.
```bash
php artisan migrate
```

## Available Methods

Below are all methods available under this package.

- `ccrisSearch(string $name, string $id, string $dob, ?string $country, ?string $id2, ?string $phone, ?string $email, ?string $address)`
    - At least one of `phone`, `email`, `address` must be present.
    - `id` argument is for New IC or Passport No.
    - `id2` argument is for old IC or Poice ID.
    - IC format XXXXXX-XX-XXXX.
    - `dob` format YYYY-MM-DD.
    - `country` default to MY.
- `checkProcessingReport(string $refNo)`
- `getRecord(string $refNo)`

## Usage

### Search CCRIS

```php
// Using service container
$experian = app('experian')->ccrisSearch(
            name: "Ali bin Ahmad",
            id: "92XXXX-XX-XXXX",
            dob: "YYYY-MM-DD",
            phone: "012XXXXXXX" 
        );  

// Using facade
$experian = \Experian::ccrisSearch(
            name: "Ali bin Ahmad",
            id: "92XXXX-XX-XXXX",
            dob: "YYYY-MM-DD",
            phone: "012XXXXXXX" 
        ); 
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

-   [Raditz Farhan](https://github.com/laraditz)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

