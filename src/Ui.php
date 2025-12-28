<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi;

/**
 * User interface.
 */
enum Ui: string
{
	/**
	 * Redoc UI.
	 *
	 * @see https://github.com/Redocly/redoc
	 */
	case REDOC = 'redoc';

	/**
	 * Elements UI.
	 *
	 * @see https://github.com/stoplightio/elements
	 */
	case ELEMENTS = 'elements';

	/**
	 * Swagger UI.
	 *
	 * @see https://github.com/swagger-api/swagger-ui
	 */
	case SWAGGER = 'swagger';

	/**
	 * Scalar UI.
	 *
	 * @see https://github.com/scalar/scalar
	 */
	case SCALAR = 'scalar';
}
