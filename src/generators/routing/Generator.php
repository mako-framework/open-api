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
use function str_starts_with;
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
	protected array $parameterPatterns =
	[
		// String formats

		'string' => [
			'no-dot' => '[^/.]++',
			'uuid'   => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
		],

		// Integer formats

		'integer' => [
			'_'              => '-?[0-9]+',
			'auto-increment' => '[1-9][0-9]{0,}',
		],
	];

	/**
	 * Merges path and operation parameters.
	 *
	 * @param \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $pathParameters
	 * @param \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $operationParameters
	 */
	public function mergeParameters(array $pathParameters, array $operationParameters): array
	{
		$merged = [];

		foreach ([$pathParameters, $operationParameters] as $parameters) {
			foreach ($parameters as $parameter) {
				$merged[$parameter->name] = $parameter;
			}
		}

		return $merged;
	}

	/**
	 * Returns the route action.
	 */
	protected function getRouteAction(string $operationId): array|string
	{
		if (strpos($operationId, '::') !== false) {
			return explode('::', $operationId, 2);
		}

		return $operationId;
	}

	/**
	 * Returns the route path.
	 *
	 * @param string                                                        $path
	 * @param \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $parameters
	 */
	protected function getRoutePath(string $path, array $parameters): string
	{
		foreach ($parameters as $parameter) {
			if ($parameter->in === 'path' && $parameter->required === false) {
				$path = str_replace("{{$parameter->name}}", "{{$parameter->name}}?", $path);
			}
		}

		return $path;
	}

	/**
	 * Returns route parameter patterns.
	 *
	 * @param \cebe\openapi\spec\Parameter[]|\cebe\openapi\spec\Reference[] $parameters
	 */
	protected function getRoutePatterns(array $parameters): array
	{
		$patterns = [];

		foreach ($parameters as $parameter) {
			if ($parameter->in === 'path') {
				if (isset($this->parameterPatterns[$parameter->schema->type][$parameter->schema->format ?? '_'])) {
					$patterns[$parameter->name] = $this->parameterPatterns[$parameter->schema->type][$parameter->schema->format ?? '_'];
				}
				elseif ($parameter->schema->format !== null && str_starts_with($parameter->schema->format, 'regex:')) {
					[, $regex] = explode(':', $parameter->schema->format);

					$patterns[$parameter->name] = $regex;
				}
			}
		}

		return $patterns;
	}

	/**
	 * Registers a route.
	 */
	abstract protected function registerRoute(string $method, string $path, array|Closure|string $action, string $name, array $patterns): void;

	/**
	 * Generates routes.
	 *
	 * @param \cebe\openapi\spec\OpenApi|SpecObjectInterface $openApi OpenApi object instance
	 */
	protected function generateRoutes(SpecObjectInterface $openApi): void
	{
		$methods = ['get', 'post', 'put', 'patch', 'delete'];

		foreach ($openApi->paths as $path => $definition) {
			foreach ($methods as $method) {
				if ($definition->{$method} !== null) {
					$parameters = $this->mergeParameters($definition->parameters, $definition->{$method}->parameters);

					$this->registerRoute(
						$method,
						$this->getRoutePath($path, $parameters),
						$this->getRouteAction($definition->{$method}->operationId),
						$definition->{$method}->operationId,
						$this->getRoutePatterns($parameters),
					);
				}
			}
		}
	}

	/**
	 * Generates routes from a yaml file.
	 */
	public function generateFromYamlFile(string $fileName): void
	{
		$this->generateRoutes(Reader::readFromYamlFile($fileName));
	}

	/**
	 * Generates routes from a yaml string.
	 */
	public function generateFromYaml(string $yaml): void
	{
		$this->generateRoutes(Reader::readFromYaml($yaml));
	}
}
