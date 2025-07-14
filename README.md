# Tapsilat PHP Package

[![CI](https://github.com/tapsilat/tapsilat-php/workflows/CI/badge.svg)](https://github.com/tapsilat/tapsilat-php/actions?query=workflow%3ACI)
[![Packagist](https://img.shields.io/packagist/v/tapsilat/tapsilat-php.svg)](https://packagist.org/packages/tapsilat/tapsilat-php)
[![Packagist Downloads](https://img.shields.io/packagist/dt/tapsilat/tapsilat-php.svg)](https://packagist.org/packages/tapsilat/tapsilat-php)

A simple PHP package that returns "tapsilat".

## Installation

You can install the package via Composer:

```bash
composer require tapsilat/tapsilat-php
```

## Usage

### Basic Usage

```php
<?php

require_once 'vendor/autoload.php';

use Tapsilat\Tapsilat;

$tapsilat = new Tapsilat();

// Get the tapsilat string
echo $tapsilat->get(); // Outputs: tapsilat

// Echo the tapsilat string directly
$tapsilat->echo(); // Outputs: tapsilat
```

### Static Usage

```php
<?php

require_once 'vendor/autoload.php';

use Tapsilat\Tapsilat;

// Use static method
echo Tapsilat::getStatic(); // Outputs: tapsilat
```

## Methods

### `get(): string`
Returns the string "tapsilat".

### `getStatic(): string`
Static method that returns the string "tapsilat".

### `echo(): void`
Echoes the string "tapsilat" directly to output.

## Requirements

- PHP >= 7.4

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Testing

Run the tests with PHPUnit:

```bash
composer test
```

## CI/CD

This package uses GitHub Actions for continuous integration and deployment:

- **CI**: Runs on every push to main/develop and pull requests
  - Tests against PHP 7.4, 8.0, 8.1, 8.2, and 8.3
  - Validates composer.json
  - Runs PHPUnit tests
  - Checks code style with PHP_CodeSniffer

- **Release**: Automatically triggered when a new tag is pushed
  - Creates a GitHub release
  - Validates and tests the package
  - Uploads release assets

- **Dependabot**: Automatically updates dependencies
  - Weekly updates for Composer packages
  - Weekly updates for GitHub Actions
  - Creates pull requests for review

- **Packagist**: Automatically publishes to Packagist
  - Triggers on GitHub releases
  - Validates and tests before publishing
  - Verifies package availability on Packagist

To create a new release:

```bash
git tag v1.0.0
git push origin v1.0.0
```

This will automatically:
1. Create a GitHub release
2. Publish the package to Packagist
3. Make it available via `composer require tapsilat/tapsilat-php`

For detailed Packagist setup instructions, see [PACKAGIST_SETUP.md](PACKAGIST_SETUP.md).

## Docker Development

This package includes Docker support for easy development setup.

### Quick Start

```bash
# Build the Docker image
./docker-dev.sh build

# Start the development container
./docker-dev.sh start

# Open a shell in the container
./docker-dev.sh shell

# Run tests
./docker-dev.sh test
```

### Available Commands

- `./docker-dev.sh build` - Build the Docker image
- `./docker-dev.sh start` - Start the development container
- `./docker-dev.sh stop` - Stop the development container
- `./docker-dev.sh restart` - Restart the development container
- `./docker-dev.sh shell` - Open a shell in the development container
- `./docker-dev.sh test` - Run tests in the container
- `./docker-dev.sh install` - Install dependencies
- `./docker-dev.sh clean` - Clean up containers and images
- `./docker-dev.sh help` - Show help message

### Manual Docker Commands

```bash
# Build and start with docker-compose
docker-compose up -d tapsilat-dev

# Run tests
docker-compose run --rm tapsilat-test

# Execute commands in the container
docker-compose exec tapsilat-dev composer install
docker-compose exec tapsilat-dev composer test
docker-compose exec tapsilat-dev php example.php
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently. 