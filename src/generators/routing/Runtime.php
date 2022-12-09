<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use Closure;
use mako\http\routing\Routes;

/**
 * Runtime route generator.
 */
class Runtime extends Generator
{
	/**
	 * Constructor.
	 *
	 * @param \mako\http\routing\Routes $routes Route collection
	 */
	public function __construct(
		protected Routes $routes
	)
	{}

	/**
	 * {@inheritDoc}
	 */
	protected function registerRoute(string $method, string $path, array|Closure|string $action, string $name): void
	{
		$this->routes->{$method}($path, $action, $name);
	}
}
