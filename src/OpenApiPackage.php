<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi;

use mako\application\Package;
use mako\openapi\console\commands\GenerateRoutes;
use mako\openapi\console\commands\GenerateSpec;

/**
 * OpenAPI package.
 */
class OpenApiPackage extends Package
{
	/**
	 * {@inheritDoc}
	 */
	protected string $packageName = 'mako/open-api';

	/**
	 * {@inheritDoc}
	 */
	protected array $commands = [
		'open-api:generate-spec'   => GenerateSpec::class,
		'open-api:generate-routes' => GenerateRoutes::class,
	];
}
