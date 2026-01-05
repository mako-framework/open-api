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
use mako\openapi\generators\routing\Cached;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;

use function pathinfo;
use function realpath;
use function rtrim;

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */
#[CommandDescription('Generates routes based on OpenApi specification file.')]
#[CommandArguments(
	new Argument('-i|--input', 'The path to the OpenApi specification file you want to generate routes from.', Argument::IS_OPTIONAL),
	new Argument('-o|--output', 'The path to where you want to store the generated route file.', Argument::IS_OPTIONAL),
)]
class GenerateRoutes extends Command
{
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
	 * Returns path to the input file.
	 */
	protected function getInputFilePath(?string $input): string
	{
		if ($input) {
			return realpath($input);
		}

		return $this->app->getPath() . '/http/routing/openapi.yaml';
	}

	/**
	 * Generates routes.
	 */
	public function execute(?string $input = null, ?string $output = null): int
	{
		$input = $this->getInputFilePath($input);

		if (!$this->fileSystem->has($input)) {
			$this->error("The [ {$input} ] specification file does not exist.");

			return static::STATUS_ERROR;
		}

		if ($output === null) {
			$path = "{$this->app->getPath()}/http/routing";
		}
		else {
			$path = rtrim($output, '/\\');
		}

		$routesFile = "{$path}/" . pathinfo($input, PATHINFO_FILENAME) . '.php';

		(new Cached($this->fileSystem, $routesFile))->generateFromYamlFile($input);

		$this->nl();
		$this->write("Successfully wrote routes to \"<yellow>{$routesFile}</yellow>\".");
		$this->nl();

		return static::STATUS_SUCCESS;
	}
}
