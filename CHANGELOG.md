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
