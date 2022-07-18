# Twig Scanner

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Total Downloads][ico-downloads]][link-downloads]

Created by Raphaël Droz <raphael.droz@gmail.com> (GPL-3 License)

Twig code scanner to use with [gettext/gettext](https://github.com/php-gettext/Gettext)

## Warning

In order to be usable, upstream ParsedFunction class at php-gettext/Gettext must be slightly modified to ease inheritance.

## Installation

```
composer require gettext/twig-scanner
```

## About dependencies

We do not require a **specific** version of Twig.

ToDo: Support symfony/twig-bridge `trans` filter.

## Usage example

```php
use Gettext\Scanner\TwigScanner;
use Gettext\Generator\PoGenerator;
use Gettext\Translations;

//Create a new scanner, adding a translation for each domain we want to get:
$twigScanner = new TwigScanner(
    Translations::create('domain1'),
    Translations::create('domain2'),
    Translations::create('domain3')
);

//Set a default domain, so any translations with no domain specified, will be added to that domain
$twigScanner->setDefaultDomain('domain1');

//Extract all comments starting with 'notes:'
$twigScanner->extractCommentsStartingWith('notes:');

//Scan files
foreach (glob('*.twig') as $file) {
    $twigScanner->scanFile($file);
}

//Save the translations in .po files
$generator = new PoGenerator();

foreach ($twigScanner->getTranslations() as $domain => $translations) {
    $generator->generateFile($translations, "locales/{$domain}.po");
}
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

The GPL-3 License (GPL-3). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/gettext/twig-scanner.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/gettext/twig-scanner.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gettext/twig-scanner
[link-downloads]: https://packagist.org/packages/gettext/twig-scanner
