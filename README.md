# OpenApi

[![Static analysis](https://github.com/mako-framework/open-api/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/mako-framework/open-api/actions/workflows/static-analysis.yml)

The `mako/open-api` package allows you to generate a [OpenApi](https://www.openapis.org) specification using [attributes](https://www.php.net/manual/en/language.attributes.php) (PHP 8.0+) or docblock annotations and to generate routes based on the specification.

## Requirements

Mako 9.0 or greater.

## Installation

First you'll need to install the package as a dependency to your project.

```
composer require mako/open-api
```

Next you'll need to add the package to the `cli` section of the `packages` configuration array in the `application.php` config file.

```
'cli' =>
[
	mako\openapi\OpenApiPackage::class,
]
```

And finally replace the contents of your `routes.php` file with the following:

```php
<?php

use mako\openapi\generators\routing\Runtime;

if(file_exists(__DIR__ . '/openapi.php'))
{
	include __DIR__ . '/openapi.php';
}
else
{
	(new Runtime($routes))->generateFromYamlFile(dirname(MAKO_APPLICATION_PATH) . '/openapi.yml');
}
```

## Usage

...
