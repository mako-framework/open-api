<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use Closure;
use mako\openapi\parser\Operation;
use mako\openapi\parser\Parameter;
use mako\openapi\parser\Parser;
use Symfony\Component\Yaml\Yaml;

use function explode;
use function in_array;
use function str_replace;
use function strpos;

/**
 * Base route generator.
 */
abstract class Generator
{
	/**
	 * Route methods.
	 */
	protected const array ROUTE_METHODS = [
		'delete',
		'get',
		'patch',
		'post',
		'put',
		'query',
	];

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

		// Number formats

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
	 * Returns the route path.
	 *
	 * @param string      $path
	 * @param Parameter[] $parameters
	 */
	protected function getRoutePath(string $path, array $parameters): string
	{
		foreach ($parameters as $parameter) {
			if ($parameter->required === false) {
				$path = str_replace("{{$parameter->name}}", "{{$parameter->name}}?", $path);
			}
		}

		return $path;
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
	 * Returns route parameter patterns.
	 *
	 * @param Parameter[] $parameters
	 */
	protected function getRoutePatterns(array $parameters): array
	{
		$patterns = [];

		foreach ($parameters as $parameter) {
			if (isset($parameter->schema['type'])) {
				$type = $parameter->schema['type'];

				if ($type === 'string' && !empty($parameter->schema['pattern'])) {
					$patterns[$parameter->name] = $parameter->schema['pattern'];
				}
				else {
					$format = $parameter->schema['format'] ?? '_';

					if (isset($this->parameterPatterns[$type][$format])) {
						$patterns[$parameter->name] = $this->parameterPatterns[$type][$format];
					}
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
	 * @param Operation[] $spec
	 */
	protected function generateRoutes(array $spec): void
	{
		foreach ($spec as $operation) {
			if (!in_array($operation->method, static::ROUTE_METHODS, true)) {
				continue;
			}

			$this->registerRoute(
				$operation->method,
				$this->getRoutePath($operation->path, $operation->pathParameters),
				$this->getRouteAction($operation->operationId),
				$operation->operationId,
				$this->getRoutePatterns($operation->pathParameters)
			);
		}
	}

	/**
	 * Generates routes from a yaml file.
	 */
	public function generateFromYamlFile(string $fileName): void
	{
		$this->generateRoutes((new Parser(Yaml::parseFile($fileName)))->parse());
	}

	/**
	 * Generates routes from a yaml string.
	 */
	public function generateFromYaml(string $yaml): void
	{
		$this->generateRoutes((new Parser(Yaml::parse($yaml)))->parse());
	}
}
