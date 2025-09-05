### 3.0.1 <small>(2025-09-05)</small>

#### Updates

* Updated swagger UI to the latest version.

--------------------------------------------------------

### 3.0.0 <small>(2025-01-03)</small>

#### Changes

* Bumped requirements to Mako 11+ and PHP 8.4+

--------------------------------------------------------

### 2.2.1 <small>(2024-10-29)</small>

#### Fixed

* Ensure that the swagger docs work if "clean URLs" are disabled.

--------------------------------------------------------

### 2.2.0 <small>(2023-11-23)</small>

#### New

* Added a controller that exposes three endpoints:
	- The OpenApi spec
	- A Redoc UI
	- A Swagger UI
* Added a new route registrar class:
	- Registers routes from a cache file or at runtime from the OpenApi yaml spec.
	- Automatically registers `/openapi/spec` route that exposes the OpenApi spec.
	- Automatically registers `/openapi/docs` route that exposes a Swagger or Redoc UI.

--------------------------------------------------------

### 2.1.0 <small>(2023-11-23)</small>

#### New

* Now possible to specify the OpenApi version when generating the spec (3.0.0 or 3.1.0).

#### Fixed

* The spec generator now supports the new Mako 10 directory structure as well as the legacy structure.

--------------------------------------------------------

### 2.0.0 <small>(2023-11-23)</small>

#### Changes

* Bumped requirements to Mako 10+ and PHP 8.1+

--------------------------------------------------------

### 1.2.4 <small>(2023-04-18)</small>

#### Fixed

* A format is no longer required for integers.

--------------------------------------------------------

### 1.2.3 <small>(2023-02-15)</small>

#### Fixed

* It is now possible to specify the output path of the generated route file.

--------------------------------------------------------

### 1.2.2 <small>(2023-01-31)</small>

#### Fixed

* Parameters are now supported on both the path level and the operation level.

--------------------------------------------------------

### 1.2.1 <small>(2023-01-27)</small>

#### Fixed

* The `OpenApiTrait::getRouteName()` method now supports invokable classes.

--------------------------------------------------------

### 1.2.0 <small>(2023-01-26)</small>

#### New

* Added `no-dot` path parameter pattern.
* Added possibility of defining custom regex based path parameter patterns.

--------------------------------------------------------

### 1.1.1 <small>(2023-01-26)</small>

#### Fixed

* Only base route parameter patterns on path parameters.

--------------------------------------------------------

### 1.1.0 <small>(2023-01-26)</small>

#### New

* Added a helper trait with the following methods:
	- `OpenApiTrait::getRouteName()`
* Added experimental support for route parameter patterns based on parameter formats.

#### Fixed

* Path parameters are parsed on path level instead of on request method level.

--------------------------------------------------------

### 1.0.0 <small>(2022-12-22)</small>

Initial release 🎉
