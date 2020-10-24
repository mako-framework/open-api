<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi;

use mako\application\Package;
use mako\openapi\console\commands\Generate;

/**
 * OpenAPI package.
 *
 * @author  Frederic G. Østby
 */
class OpenApiPackage extends Package
{
	/**
	 * {@inheritdoc}
	 */
	protected $packageName = 'mako/open-api';

	/**
	 * {@inheritdoc}
	 */
	protected $commands =
	[
		'open-api.generate' => Generate::class,
	];
}
