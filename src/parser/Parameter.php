<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\parser;

/**
 * Parameter.
 */
readonly class Parameter
{
	public function __construct(
		public string $name,
		public bool $required,
		public array $schema
	) {
	}
}
