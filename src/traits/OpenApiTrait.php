<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\traits;

/**
 * OpenApi trait.
 */
trait OpenApiTrait
{
    /**
     * Returns the route name.
     */
    protected function getRouteName(string $class, ?string $method = null): string
    {
        return $method === null ? $class : "{$class}::{$method}";
    }
}
