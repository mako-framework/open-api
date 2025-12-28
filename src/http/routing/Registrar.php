<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\http\routing;

use mako\http\routing\Routes;
use mako\openapi\generators\routing\Runtime;
use mako\openapi\http\controllers\Documentation;
use mako\openapi\Ui;

use function file_exists;

/**
 * Route registrar.
 */
class Registrar
{
	/**
	 * Registers the routes.
	 */
	public static function register(
		Routes $routes,
		string $cachedRoutes,
		string $openApiSpec,
		?string $specPath = '/openapi/spec',
		?string $docsPath = '/openapi/docs',
		Ui $ui = Ui::SWAGGER,
		string $uiOptions = '{}'
	): void {
		// Include the cached routes if they exist. Otherwise we'll generate them at runtime.

		if (file_exists($cachedRoutes)) {
			include $cachedRoutes;
		}
		else {
			(new Runtime($routes))->generateFromYamlFile($openApiSpec);
		}

		// Register the spec and documentation routes

		if ($specPath !== null) {
			Documentation::setOpenApiSpecPath($openApiSpec);

			$routes->get($specPath, [Documentation::class, 'openapi'], 'mako:openapi:spec');

			if ($docsPath !== null) {
				Documentation::setUiOptions($uiOptions);

				$routes->get($docsPath, [Documentation::class, $ui->value], 'mako:openapi:docs');
			}
		}
	}
}
