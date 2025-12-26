<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\spec\processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Generator;

/**
 * Operation id processor.
 */
class OperationId
{
    /**
     * Sets the operation id to the targeted method if an operation id isn't specified.
     */
    public function __invoke(Analysis $analysis): void
    {
        /** @var Operation $operation */
        foreach ($analysis->getAnnotationsOfType(Operation::class) as $operation) {
            if (!empty($operation->operationId) && $operation->operationId !== Generator::UNDEFINED) {
               continue;
            }

            $context = $operation->_context;

            if ($context) {
				$operationId = "{$context->namespace}\\{$context->class}";

				if ($context->method && $context->method !== '__invoke') {
					$operationId .= "::{$context->method}";
				}

                $operation->operationId = $operationId;
            }
        }
    }
}
