<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\console\commands;

use mako\cli\input\arguments\Argument;
use mako\reactor\Command;

use function OpenApi\scan;

/**
 * Command that generates OpenApi documentation.
 *
 * @author Frederic G. Østby
 */
class Generate extends Command
{
	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-j|--json', 'Output documentation as JSON instead of YAML.', Argument::IS_BOOL),
		];
	}

	/**
	 * Generates the API documentation.
	 *
	 * @param bool $json Should we output as JSON?
	 */
	public function execute(bool $json = false): void
	{
		$openapi = scan($this->app->getPath());

		$extension = $json ? 'json' : 'yaml';

		$this->fileSystem->put("{$this->app->getPath()}/openapi.{$extension}", $json ? $openapi->toJson() : $openapi->toYaml());
	}
}
