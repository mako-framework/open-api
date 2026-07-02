<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\parser;

/**
 * Operation.
 */
readonly class Operation
{
	/**
	 * Constructor.
	 *
	 * @param Parameter[] $pathParameters
	 * @param Parameter[] $queryParameters
	 */
	public function __construct(
		public string $path,
		public string $method,
		public string $operationId,
		public array $pathParameters = [],
		public array $queryParameters = []
	) {
	}
}
