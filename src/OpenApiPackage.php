<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi;

use mako\application\Package;
use mako\openapi\console\commands\GenerateRoutes;
use mako\openapi\console\commands\GenerateSpec;
use Override;

/**
 * OpenAPI package.
 */
class OpenApiPackage extends Package
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $packageName = 'mako/open-api';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected array $commands = [
		'open-api:generate-spec'   => GenerateSpec::class,
		'open-api:generate-routes' => GenerateRoutes::class,
	];
}
