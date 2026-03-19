# Fantastico VPS Addon for MyAdmin

[![Tests](https://github.com/detain/myadmin-fantastico-vps-addon/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-fantastico-vps-addon/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-fantastico-vps-addon/version)](https://packagist.org/packages/detain/myadmin-fantastico-vps-addon)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-fantastico-vps-addon/downloads)](https://packagist.org/packages/detain/myadmin-fantastico-vps-addon)
[![License](https://poser.pugx.org/detain/myadmin-fantastico-vps-addon/license)](https://packagist.org/packages/detain/myadmin-fantastico-vps-addon)

A MyAdmin plugin that provides Fantastico license provisioning as an addon for the VPS hosting module. It integrates with the Symfony EventDispatcher to handle license activation, deactivation, and settings management within the MyAdmin control panel.

## Features

- Sell Fantastico licenses as a VPS addon
- Automatic license activation and deactivation via the Fantastico licensing API
- Configurable pricing through the MyAdmin settings interface
- Event-driven architecture using Symfony EventDispatcher hooks

## Installation

Install via Composer:

```sh
composer require detain/myadmin-fantastico-vps-addon
```

## Requirements

- PHP >= 5.0
- ext-soap
- Symfony EventDispatcher ^5.0
- A valid cPanel license with cPanelDirect (required for Fantastico licensing)

## Testing

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [LGPL-2.1](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html) license.
