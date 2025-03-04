<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\http\controllers;

use mako\database\exceptions\NotFoundException;
use mako\http\Response;
use mako\http\routing\URLBuilder;

use function file_exists;
use function file_get_contents;

/**
 * Documentation controller.
 */
class Documentation
{
	/**
	 * OpenApi specification path.
	 */
	protected static ?string $openApiSpecPath = null;

	/**
	 * Sets the OpenApi specification path.
	 */
	public static function setOpenApiSpecPath(string $path): void
	{
		self::$openApiSpecPath = $path;
	}

	/**
	 * Returns the OpenApi specification path.
	 */
	public static function getOpenApiSpecPath(): string
	{
		return self::$openApiSpecPath;
	}

	/**
	 * Returns the OpenApi specification.
	 */
	public function openapi(Response $response): string
	{
		$openApiSpecPath = self::getOpenApiSpecPath();

		if (empty($openApiSpecPath) || !file_exists($openApiSpecPath)) {
			throw new NotFoundException;
		}

		$response->setType('application/yaml');

		return file_get_contents($openApiSpecPath);
	}

	/**
	 * Returns the Recoc UI.
	 */
	public function redoc(URLBuilder $uRLBuilder): string
	{
		$specUrl = $uRLBuilder->toRoute('mako:openapi:spec');

		return <<<HTML
		<!DOCTYPE html>
		<html lang="en">
		<html>
			<head>
				<meta charset="UTF-8">
				<title>OpenApi - Redoc</title>
			</head>
			<body>
				<redoc spec-url="$specUrl"></redoc>
				<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
			</body>
		</html>
		HTML;
	}

	/**
	 * Returns the Swagger UI.
	 */
	public function swagger(URLBuilder $uRLBuilder): string
	{
		$specUrl = $uRLBuilder->toRoute('mako:openapi:spec');
		$apiBaseUrl = $uRLBuilder->to('/');

		return <<<HTML
		<!DOCTYPE html>
		<html lang="en">
		<html>
			<head>
				<meta charset="UTF-8">
				<title>OpenApi - Swagger</title>
				<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui.min.css">
			</head>
			<body>
				<div id="swagger-ui"></div>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui-bundle.min.js"></script>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui-standalone-preset.min.js"></script>
				<script type="text/javascript">
					const SetServerPlugin = (swagger) => ({
						rootInjects: {
							setServer: (server) => {
								const spec = swagger.getState().toJSON().spec.json;
								if (!spec.servers) {
									const servers = [{url: server, description: 'Current server'}];
									const newSpec = Object.assign({}, spec, { servers });
									swagger.specActions.updateJsonSpec(newSpec);
								}
							}
						}
					});

					window.onload = () => {
						const ui = SwaggerUIBundle({
							url: "$specUrl",
							dom_id: "#swagger-ui",
							deepLinking: true,
							presets: [
								SwaggerUIBundle.presets.apis,
								SwaggerUIStandalonePreset
							],
							plugins: [
								SwaggerUIBundle.plugins.DownloadUrl,
								SetServerPlugin
							],
							layout: "BaseLayout",
							onComplete: () => {
								window.ui.setServer("$apiBaseUrl");
							}
						});

						window.ui = ui;
					};
				</script>
			</body>
		</html>
		HTML;
	}
}
