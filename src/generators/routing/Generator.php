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
	protected array $parameterPatterns =
	[
		// String formats

		'string' => [
			'no-dot'    => '[^/.]++',
			'uuid'      => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
			'date'      => '(19|20)\d\d-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])',
			'date-time' => '((?:(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2}(?:\.\d+)?))(Z|[\+-]\d{2}:\d{2})?)',
			'byte'      => '(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})',
		],

		'number' => [
			'_'      => '-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?',
			'float'  => '-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?',
			'double' => '-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?',
		],

		// Integer formats

		'integer' => [
			'_'              => '-?[0-9]+',
			'int32'          => '-?[0-9]+',
			'int64'          => '-?[0-9]+',
			'auto-increment' => '[1-9][0-9]{0,}',
		],

		// Bolean formats

		'boolean' => [
			'_' => 'true|false',
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
				if ($parameter->schema->type === 'string' && !empty($parameter->schema->pattern)) {
					$patterns[$parameter->name] = $parameter->schema->pattern;
				}
				elseif (isset($this->parameterPatterns[$parameter->schema->type][$parameter->schema->format ?? '_'])) {
					$patterns[$parameter->name] = $this->parameterPatterns[$parameter->schema->type][$parameter->schema->format ?? '_'];
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
