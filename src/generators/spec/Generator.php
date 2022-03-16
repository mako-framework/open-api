<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\spec;

use mako\file\FileSystem;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator as OpenApiGenerator;
use OpenApi\Util;
use Symfony\Component\Finder\Finder;

/**
 * Base route generator.
 */
class Generator
{
	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Path to the output file.
	 *
	 * @var string
	 */
	protected $outputFile;

	/**
	 * Director(y|ies) or file(s) to scan.
	 *
	 * @var array|string
	 */
	protected $directory;

	/**
	 * Director(y|ies) or file(s) to exclude.
	 *
	 * @var array|string|null
	 */
	protected $exclude;

	/**
	 * Pattern of the files to scan.
	 *
	 * @var string|null
	 */
	protected $pattern;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem File system instance
	 * @param string                $outputFile Path to the output file
	 * @param array|string          $directory  Director(y|ies) or file(s) to scan
	 * @param array|string|null     $exclude    Director(y|ies) or file(s) to exclude
	 * @param string|null           $pattern    Pattern of the files to scan
	 */
	public function __construct(FileSystem $fileSystem, string $outputFile, $directory, $exclude = null, ?string $pattern = null)
	{
		$this->fileSystem = $fileSystem;

		$this->outputFile = $outputFile;

		$this->directory = $directory;

		$this->exclude = $exclude;

		$this->pattern = $pattern;
	}

	/**
	 * Returns a finder instance.
	 *
	 * @return \Symfony\Component\Finder\Finder|null
	 */
	protected function getFinder(): ?Finder
	{
		return Util::finder($this->directory, $this->exclude, $this->pattern);
	}

	/**
	 * Returns a OpenApi spec genrator.
	 *
	 * @return \OpenApi\Annotations\OpenApi|null
	 */
	protected function getGenerator(): ?OpenApi
	{
		return OpenApiGenerator::scan($this->getFinder(), ['version' => OpenApi::DEFAULT_VERSION]);
	}

	/**
	 * Writes the api specification to the specified output file.
	 */
	public function generate(): void
	{
		$this->fileSystem->put($this->outputFile, $this->getGenerator()->toYaml());
	}
}
