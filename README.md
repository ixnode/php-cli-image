# PHP Cli Image

[![Release](https://img.shields.io/github/v/release/ixnode/php-cli-image)](https://github.com/ixnode/php-cli-image/releases)
[![](https://img.shields.io/github/release-date/ixnode/php-cli-image)](https://github.com/ixnode/php-cli-image/releases)
![](https://img.shields.io/github/repo-size/ixnode/php-cli-image.svg)
[![PHP](https://img.shields.io/badge/PHP-^8.2-777bb3.svg?logo=php&logoColor=white&labelColor=555555&style=flat)](https://www.php.net/supported-versions.php)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-777bb3.svg?style=flat)](https://phpstan.org/user-guide/rule-levels)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-Unit%20Tests-6b9bd2.svg?style=flat)](https://phpunit.de)
[![PHPCS](https://img.shields.io/badge/PHPCS-PSR12-416d4e.svg?style=flat)](https://www.php-fig.org/psr/psr-12/)
[![PHPMD](https://img.shields.io/badge/PHPMD-ALL-364a83.svg?style=flat)](https://github.com/phpmd/phpmd)
[![Rector - Instant Upgrades and Automated Refactoring](https://img.shields.io/badge/Rector-PHP%208.2-73a165.svg?style=flat)](https://github.com/rectorphp/rector)
[![LICENSE](https://img.shields.io/github/license/ixnode/php-api-version-bundle)](https://github.com/ixnode/php-api-version-bundle/blob/master/LICENSE)

> This library prints a given image to cli as ascii string.

## 1. Usage

```php
use Ixnode\PhpCliImage\CliImage;
```

### 1.1 Simple example

```php
$file = new File('path/to/image');
$width = 80;
$image = new CliImage($file);

print $image->getAsciiString($width);
```

## 2. Installation

```bash
composer require ixnode/php-cli-image
```

```bash
vendor/bin/php-cli-image -V
```

```bash
php-cli-image 0.1.0 (03-07-2023 01:17:26) - Björn Hempel <bjoern@hempel.li>
```

## 3. Command line tool

> Used to quickly check the image output of given image.

```bash
bin/console ci docs/image/world-map.png --engine=gd-image --width=80
```

or within your composer project:

```bash
vendor/bin/php-cli-image ci vendor/ixnode/php-cli-image/docs/image/world-map.png
```

```bash

                     ▄▄▄▄▀▄▄▄▄▄▄▄▄▄▄
       ▄▄    ▄▄▄▄▄▄▄▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀                ▄▄▄▄▀▀▀▀▀▄▄▄▄▄▄▄▄▄▄
  ▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀ ▀▀▀▀▀▀▀▀      ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀
 ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀   ▀▀          ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀
       ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀           ▄▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▄ ▀▀
     ▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀            ▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀
     ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀               ▀▀▀▀▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▄
     ▀▀▀▀▀▀▀▀▀▀▀▀                 ▀▀▀▀▀▀▀▀▀▀▄▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀
      ▀▀▀▀▀▀▄▄ ▀                ▄▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀
        ▀▀▀▀▀▀                  ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀▀▀
           ▀▀▀▄▄▀▀▄▄▄           ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀      ▀▀    ▀▀▀▀
              ▀▀▀▀▀▀▀▀▀▄         ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀            ▀▄  ▄▀▀
              ▀▀▀▀▀▀▀▀▀▀▀▄▄▄           ▀▀▀▀▀▀▀▀▀▀▀              ▀▀▀▀▀▀▀   ▀▄▄▄▄
              ▀▀▀▀▀▀▀▀▀▀▀▀▀▀            ▀▀▀▀▀▀▀▀▀                 ▀        ▀▀▀▀▄
               ▀▀▀▀▀▀▀▀▀▀▀▀             ▀▀▀▀▀▀▀▀▀▄▀▀                   ▄▀▀▀▀▀▀
                  ▀▀▀▀▀▀▀▀▀             ▀▀▀▀▀▀▀▀ ▀▀                 ▀▀▀▀▀▀▀▀▀▀▀
                  ▀▀▀▀▀▀▀                ▀▀▀▀▀▀  ▀                  ▀▀▀▀▀▀▀▀▀▀▀
                  ▀▀▀▀▀▀                  ▀▀▀                       ▀▀▀▀▀▀▀▀▀▀▀
                  ▀▀▀▀                                                     ▀
                   ▀▀▀
```

<img src="docs/image/world-map-cli.png" alt="Services" width="800"/>

## 4. Library development

```bash
git clone git@github.com:ixnode/php-cli-image.git && cd php-cli-image
```

```bash
composer install
```

```bash
composer test
```

## 5. License

This library is licensed under the MIT License - see the [LICENSE](/LICENSE) file for details.
