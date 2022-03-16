<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\routing;

use mako\file\FileSystem;

/**
 * Cached route generator.
 */
class Cached extends Generator
{
	/**
	 * Output file.
	 *
	 * @var \SplFileObject
	 */
	protected $outputFile;

	/**
	 * Constructor. File system instance.
	 *
	 * @param \mako\file\FileSystem $fileSystem File system instance
	 * @param string                $outputFile Output file
	 */
	public function __construct(FileSystem $fileSystem, string $outputFile)
	{
		$this->outputFile = $fileSystem->file($outputFile, 'w');

		$this->outputFile->fwrite(<<<'PHP'
		<?php


		PHP);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function registerRoute(string $method, string $path, $action): void
	{
		$action = var_export($action, true);

		$this->outputFile->fwrite(<<<PHP
		\$routes->{$method}('$path', $action);


		PHP);
	}
}