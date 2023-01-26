<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use cebe\openapi\Reader;
use cebe\openapi\SpecObjectInterface;
use Closure;

use function explode;
use function str_replace;
use function strpos;

/**
 * Base route generator.
 */
abstract class Generator
{
	/**
	 * Parameter patterns.
	 *
	 * @var string[][]
	 */
	protected $parameterPatterns =
	[
		'string' =>
		[
			'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
		],
	];

	/**
	 * Returns the route action.
	 *
	 * @param  string       $operationId Operation id
	 * @return array|string
	 */
	protected function getRouteAction(string $operationId): array|string
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
	 * Returns route parameter patterns.
	 *
	 * @param  \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $parameters Route parameters
	 * @return array
	 */
	protected function getRoutePatterns(array $parameters): array
	{
		$patterns = [];

		foreach($parameters as $parameter)
		{
			if($parameter->schema->format !== null && isset($this->parameterPatterns[$parameter->schema->type][$parameter->schema->format]))
			{
				$patterns[$parameter->name] = $this->parameterPatterns[$parameter->schema->type][$parameter->schema->format];
			}
		}

		return $patterns;
	}

	/**
	 * Registers a route.
	 *
	 * @param string                $method   HTTP method
	 * @param string                $path     Route path
	 * @param array|\Closure|string $action   Route action
	 * @param string                $name     Route name
	 * @param array                 $patterns Parameter patterns
	 */
	abstract protected function registerRoute(string $method, string $path, array|Closure|string $action, string $name, array $patterns): void;

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
						$this->getRoutePath($path, $definition->parameters),
						$this->getRouteAction($definition->{$method}->operationId),
						$definition->{$method}->operationId,
						$this->getRoutePatterns($definition->parameters),
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
