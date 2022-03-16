<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use mako\http\routing\Routes;

/**
 * Runtime route generator.
 */
class Runtime extends Generator
{
	/**
	 * Route collection.
	 *
	 * @var \mako\http\routing\Routes
	 */
	protected $routes;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\routing\Routes $routes Route collection
	 */
	public function __construct(Routes $routes)
	{
		$this->routes = $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function registerRoute(string $method, string $path, $action): void
	{
		$this->routes->{$method}($path, $action);
	}
}
