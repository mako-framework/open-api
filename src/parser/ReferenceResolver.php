<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\parser;

use mako\openapi\parser\exceptions\ParserException;

use function array_pop;
use function explode;
use function in_array;
use function is_array;
use function sprintf;
use function str_starts_with;
use function substr;

/**
 * Reference resolver.
 */
final class ReferenceResolver
{
    /**
     * Cache of resolved refs.
     */
    protected array $cache = [];

    /**
     * Tracks resolution stack (for circular refs).
     */
    protected array $stack = [];

    /**
     * Constructor.
     */
    public function __construct(
        protected array $spec
    ) {
    }

    /**
     * Traverse spec using JSON-pointer style path.
     */
    protected function traverse(string $ref): mixed
    {
        $path = explode('/', substr($ref, 2));

        $node = $this->spec;

        foreach ($path as $segment) {
            if (!isset($node[$segment])) {
                throw new ParserException(sprintf('Invalid reference [ %s ]. The reference does not exist.', $ref));
            }

            $node = $node[$segment];
        }

        return $node;
    }

    /**
     * Recursively resolve nested $refs inside arrays.
     */
    protected function resolveNested(mixed $node): mixed
    {
        if (is_array($node)) {

            if (isset($node['$ref'])) {
                return $this->resolve($node['$ref']);
            }

            foreach ($node as $key => $value) {
                $node[$key] = $this->resolveNested($value);
            }
        }

        return $node;
    }

    /**
     * Resolve a $ref recursively.
     */
    public function resolve(string $ref): mixed
    {
		// Check if reference is local

        if (!str_starts_with($ref, '#/')) {
            throw new ParserException(sprintf('Invalid reference [ %s ]. Only local references supported.', $ref));
        }

        // Prevent circular references

        if (in_array($ref, $this->stack, true)) {
            throw new ParserException(sprintf('Invalid reference [ %s ]. Circular reference detected.', $ref));
        }

        // Return from cache if previously resolved

        if (isset($this->cache[$ref])) {
            return $this->cache[$ref];
        }

		// Resolve and cache for later use and return

        $this->stack[] = $ref;

		try {
			$node = $this->traverse($ref);

        	$node = $this->resolveNested($node);

			return $this->cache[$ref] = $node;
		}
		finally {
			array_pop($this->stack);
		}
    }
}
