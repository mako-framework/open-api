# OpenApi

[![Static analysis](https://github.com/mako-framework/open-api/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/mako-framework/open-api/actions/workflows/static-analysis.yml)

The `mako/open-api` package allows you to generate an [OpenApi](https://www.openapis.org) specification by documenting your API routes using PHP [attributes](https://www.php.net/manual/en/language.attributes.php), and to generate routes from an OpenAPI specification.

In addition, the package can render interactive API documentation for your OpenAPI specification using one of the following user interface providers:

* Elements
* Redoc
* Scalar
* Swagger (default)

## Requirements

Mako 12.0 or greater.

## Installation

First you'll need to install the package as a dependency to your project.

```
composer require mako/open-api
```

Next you'll need to add the package to the `cli` section of the `packages` configuration array in the `application.php` config file.

```
'cli' => [
	mako\openapi\OpenApiPackage::class,
]
```

And finally replace the contents of your `routes.php` file with the following:

```php
<?php

use mako\openapi\http\routing\Registrar;

Registrar::register(
	$routes,
	cachedRoutes: __DIR__ . '/openapi.php',
	openApiSpec: __DIR__ . '/openapi.yml',
);
```

## Usage

You can build an OpenAPI specification using a tool such as [Apicurito](https://www.apicur.io/apicurito/pwa/) or by documenting your code using PHP attributes. For details on the available syntax, see the [zircote/swagger-php](https://github.com/zircote/swagger-php) documentation.

If you want to generate a specification file from your documentation, you can do so by running the `open-api:generate-spec` command.

To generate a cached route file for production, run the `open-api:generate-routes` command.

> Note: If you manually write your OpenAPI specification, you must set the `operationId` parameter to the fully qualified method name (for example, app\controllers\Index::welcome) of the controller action in order for the route generator to work.
