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
     *
     * @param  string $class  Fully qualified class name
     * @param  string $method Method name
     * @return string
     */
    protected function getRouteName(string $class, string $method): string
    {
        return "{$class}::{$method}";
    }
}
