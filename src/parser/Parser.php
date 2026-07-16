<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\parser;

use mako\openapi\parser\exceptions\ParserException;

use function in_array;
use function is_array;

/**
 * OpenApi parser.
 */
class Parser
{
	/**
	 * Request methods.
	 */
	protected const METHODS = [
		'delete',
		'get',
		'head',
		'options',
		'patch',
		'post',
		'put',
		'query',
		'trace',
	];

	/**
	 * Parameter buckets.
	 */
	protected const PARAMETER_BUCKETS = [
		'cookie',
		'header',
		'path',
		'query',
	];

	/**
	 * Reference resolver.
	 */
	protected ReferenceResolver $referenceResolver;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $spec
	) {
		$this->referenceResolver = new ReferenceResolver($this->spec);
	}

	/**
	 * Normalize parameter.
	 */
	protected function normalizeParameter(array $param): array
	{
		if (isset($param['$ref'])) {
			$param = $this->referenceResolver->resolve($param['$ref']);
		}

		return $param;
	}

	/**
	 * Resolves $ref inside schema recursively.
	 */
	protected function resolveSchema(mixed $schema): mixed
	{
		if (!is_array($schema)) {
			return $schema;
		}

		if (isset($schema['$ref'])) {
			return $this->referenceResolver->resolve($schema['$ref']);
		}

		foreach ($schema as $key => $value) {
			$schema[$key] = $this->resolveSchema($value);
		}

		return $schema;
	}

	/**
	 * Parses parameters.
	 */
	protected function parseParameters(array $pathParams, array $methodParams): array
	{
		$indexed = [];

		$process = function (array $params) use (&$indexed): void {
			foreach ($params as $param) {

				$param = $this->normalizeParameter($param);

				if (!in_array($param['in'], self::PARAMETER_BUCKETS, true)) {
					continue;
				}

				$indexed["{$param['name']}:{$param['in']}"] = [
					'name' => $param['name'],
					'in' => $param['in'],
					'required' => $param['required'] ?? ($param['in'] === 'path' ? true : false),
					'schema' => $this->resolveSchema($param['schema'] ?? []),
				];
			}
		};

		$process($pathParams);
		$process($methodParams);

		$path = [];
		$query = [];
		$cookie = [];
		$header = [];

		foreach ($indexed as $param) {
			$object = new Parameter(
				$param['name'],
				$param['required'],
				$param['schema']
			);

			${$param['in']}[] = $object;
		}

		return [$path, $query, $cookie, $header];
	}

	/**
	 * Parses spec into operation objects.
	 *
	 * @return Operation[]
	 */
	public function parse(): array
	{
		$operations = [];

		if (!isset($this->spec['paths']) || !is_array($this->spec['paths'])) {
			throw new ParserException("The OpenAPI document does not contain a valid 'paths' section.");
		}

		foreach ($this->spec['paths'] as $path => $pathSpec) {
			foreach (static::METHODS as $method) {
				if (!isset($pathSpec[$method])) {
            		continue;
        		}

				$methodSpec = $pathSpec[$method];

				[$pathParameters, $queryParameters, $cookies, $headers] = $this->parseParameters(
					$pathSpec['parameters'] ?? [],
					$methodSpec['parameters'] ?? []
				);

				$operations[] = new Operation(
					$path,
					$method,
					$methodSpec['operationId'] ?? throw new ParserException("Missing required 'operationId' for {$method}:{$path}."),
					$pathParameters,
					$queryParameters,
					$cookies,
					$headers
				);
			}
		}

		return $operations;
	}
}
