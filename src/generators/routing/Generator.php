<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use cebe\openapi\Reader;
use cebe\openapi\SpecObjectInterface;

use function explode;
use function str_replace;
use function strpos;

/**
 * Base route generator.
 */
abstract class Generator
{
	/**
	 * Returns the route action.
	 *
	 * @param  string       $operationId Operation id
	 * @return array|string
	 */
	protected function getRouteAction(string $operationId)
	{
		if(strpos($operationId, '::') !== false)
		{
			return explode('::', $operationId, 2);
		}

		return $operationId;
	}

	/**
	 * Returns the route path.
	 *
	 * @param  string                                                        $path       Route path
	 * @param  \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $parameters Route parameters
	 * @return string
	 */
	protected function getRoutePath(string $path, array $parameters): string
	{
		foreach($parameters as $parameter)
		{
			if($parameter->in === 'path' && $parameter->required === false)
			{
				$path = str_replace("{{$parameter->name}}", "{{$parameter->name}}?", $path);
			}
		}

		return $path;
	}

	/**
	 * Registers a route.
	 *
	 * @param string $method HTTP method
	 * @param string $path   Route path
	 * @param mixed  $action Route action
	 */
	abstract protected function registerRoute(string $method, string $path, $action): void;

	/**
	 * Generates routes.
	 *
	 * @param \cebe\openapi\spec\OpenApi|\cebe\openapi\SpecObjectInterface $openApi OpenApi object instance
	 */
	protected function generateRoutes(SpecObjectInterface $openApi): void
	{
		$methods = ['get', 'post', 'put', 'patch', 'delete'];

		foreach($openApi->paths as $path => $definition)
		{
			foreach($methods as $method)
			{
				if($definition->{$method} !== null)
				{
					$this->registerRoute(
						$method,
						$this->getRoutePath($path, $definition->{$method}->parameters),
						$this->getRouteAction($definition->{$method}->operationId)
					);
				}
			}
		}
	}

	/**
	 * Generates routes from a yaml file.
	 *
	 * @param string $fileName Path to OpenApi file
	 */
	public function generateFromYamlFile(string $fileName): void
	{
		$this->generateRoutes(Reader::readFromYamlFile($fileName));
	}

	/**
	 * Generates routes from a yaml string.
	 *
	 * @param string $yaml Yaml string
	 */
	public function generateFromYaml(string $yaml): void
	{
		$this->generateRoutes(Reader::readFromYaml($yaml));
	}
}
