<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\console\commands;

use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\file\FileSystem;
use mako\openapi\generators\spec\Generator as SpecGenerator;
use mako\reactor\Command;
use OpenApi\Annotations\OpenApi;

use function dirname;
use function strpos;

/**
 * Command that generates OpenApi specification file.
 */
class GenerateSpec extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Generates OpenAPI specification file.';

	/**
	 * Constructor.
	 */
	public function __construct(
		Input $input,
		Output $output,
		protected Application $app,
		protected FileSystem $fileSystem
	) {
		parent::__construct($input, $output);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return [
			new Argument('-f|--filename', 'The filename you want to use for the documentation (default: openapi).', Argument::IS_OPTIONAL),
			new Argument('-s|--scan', 'The director(y|ies) you want to scan (default: app).', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
			new Argument('-e|--exclude', 'The director(y|ies) or filename(s) to exclude (as absolute or relative paths).', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
			new Argument('-p|--pattern', 'File pattern to scan (default: *.php).', Argument::IS_OPTIONAL),
			new Argument('-o|--output', 'The output directory where you want to save the OpenAPI documentation (default: project root).', Argument::IS_OPTIONAL),
			new Argument('-v|--version', 'The OpenAPI version to use (default: ' . OpenApi::DEFAULT_VERSION . ').', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Returns the paths to scan.
	 */
	protected function getScanPaths(?array $paths): array
	{
		if (empty($paths)) {
			if ($this->fileSystem->has("{$this->app->getPath()}/controllers")) {
				$controllers = "{$this->app->getPath()}/controllers";
			} else {
				$controllers = "{$this->app->getPath()}/http/controllers";
			}

			return [$controllers, "{$this->app->getPath()}'/models"];
		}

		$root = dirname($this->app->getPath());

		foreach ($paths as $key => $value) {
			if (strpos($value, DIRECTORY_SEPARATOR) !== 0) {
				$paths[$key] = "{$root}/{$value}";
			}
		}

		return $paths;
	}

	/**
	 * Returns the output path.
	 */
	protected function getOutputPath(?string $path): string
	{
		$root = dirname($this->app->getPath());

		if (empty($path)) {
			return "{$root}/http/routing";
		}

		return strpos($path, DIRECTORY_SEPARATOR) === 0 ? $path : "{$root}/{$path}";
	}

	/**
	 * Generates the API documentation.
	 */
	public function execute(string $filename = 'openapi', ?array $scan = null, ?array $exclude = null, ?string $pattern = null, ?string $output = null, string $version = OpenApi::DEFAULT_VERSION): void
	{
		$output = "{$this->getOutputPath($output)}/{$filename}.yml";

		$generator = new SpecGenerator(
			$this->fileSystem,
			$output,
			$this->getScanPaths($scan),
			$exclude,
			$pattern,
			$version
		);

		$generator->generate();

		$this->write("Successfully wrote OpenApi specification to [ {$output} ].");
	}
}
