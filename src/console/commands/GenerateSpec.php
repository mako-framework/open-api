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

use function dirname;
use function strpos;

/**
 * Command that generates OpenApi specification file.
 */
class GenerateSpec extends Command
{
	/**
	 * Application instance.
	 *
	 * @var \mako\application\cli\Application
	 */
	protected $app;

	/**
	 * FileSystem instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generates OpenAPI specification file.';

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input         $input      Input
	 * @param \mako\cli\output\Output       $output     Output
	 * @param \mako\application\Application $app        Application instance
	 * @param \mako\file\FileSystem         $fileSystem FileSystem instance
	 */
	public function __construct(Input $input, Output $output, Application $app, FileSystem $fileSystem)
	{
		parent::__construct($input, $output);

		$this->app = $app;

		$this->fileSystem = $fileSystem;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-f|--filename', 'The filename you want to use for the documentation (default: openapi).', Argument::IS_OPTIONAL),
			new Argument('-s|--scan', 'The director(y|ies) you want to scan (default: app).', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
			new Argument('-e|--exclude', 'The director(y|ies) or filename(s) to exclude (as absolute or relative paths).', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
			new Argument('-p|--pattern', 'File pattern to scan (default: *.php).', Argument::IS_OPTIONAL),
			new Argument('-o|--output', 'The output directory where you want to save the OpenAPI documentation (default: project root).', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Returns the paths to scan.
	 *
	 * @param  array|null $paths Array of paths to scan
	 * @return array
	 */
	protected function getScanPaths(?array $paths): array
	{
		if(empty($paths))
		{
			return [$this->app->getPath() . '/controllers', $this->app->getPath() . '/models'];
		}

		$root = dirname($this->app->getPath());

		foreach($paths as $key => $value)
		{
			if(strpos($value, DIRECTORY_SEPARATOR) !== 0)
			{
				$paths[$key] = "{$root}/{$value}";
			}
		}

		return $paths;
	}

	/**
	 * Returns the output path.
	 *
	 * @param  string|null $path Output path
	 * @return string
	 */
	protected function getOutputPath(?string $path): string
	{
		$root = dirname($this->app->getPath());

		if(empty($path))
		{
			return $root;
		}

		return strpos($path, DIRECTORY_SEPARATOR) === 0 ? $path : "{$root}/{$path}";
	}

	/**
	 * Generates the API documentation.
	 *
	 * @param string      $filename Documentation filename
	 * @param array|null  $scan     Array of paths to scan
	 * @param array|null  $exclude  Array of paths to exclude
	 * @param string|null $pattern  Pattern to include
	 * @param string|null $output   Output path
	 */
	public function execute(string $filename = 'openapi', ?array $scan = null, ?array $exclude = null, ?string $pattern = null, ?string $output = null): void
	{
		$output = "{$this->getOutputPath($output)}/{$filename}.yml";

		(new SpecGenerator($this->fileSystem, $output, $this->getScanPaths($scan), $exclude, $pattern))->generate();

		$this->write("Successfully wrote OpenApi specification to [ {$output} ].");
	}
}
