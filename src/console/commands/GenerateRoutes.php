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
use mako\reactor\Command;

use function dirname;
use function pathinfo;
use function rtrim;

 /**
  * @copyright Frederic G. Østby
  * @license   http://www.makoframework.com/license
  */
 class GenerateRoutes extends Command
 {
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Generates routes based on OpenApi specification file.';

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
			new Argument('-i|--input', 'The path to the OpenApi specification file you want to generate routes from.', Argument::IS_OPTIONAL),
			new Argument('-o|--output', 'The path to where you want to store the generated route file.', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Returns path to the input file.
	 */
	protected function getInputFilePath(?string $input): string
	{
		return $input ?? dirname($this->app->getPath()) . '/openapi.yml';
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

		$errorReporting = error_reporting();

		error_reporting($errorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

		(new Cached($this->fileSystem, $routesFile))->generateFromYamlFile($input);

		error_reporting($errorReporting);

		$this->write("Successfully wrote routes to [ {$routesFile} ].");

		return static::STATUS_SUCCESS;
	}
 }
