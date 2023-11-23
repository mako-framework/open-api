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
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $outputFile,
		protected $directory,
		protected $exclude = null,
		protected ?string $pattern = null,
		protected string $version = OpenApi::DEFAULT_VERSION
	) {
	}

	/**
	 * Returns a finder instance.
	 */
	protected function getFinder(): Finder
	{
		return Util::finder($this->directory, $this->exclude, $this->pattern);
	}

	/**
	 * Returns a OpenApi spec genrator.
	 */
	protected function getGenerator(): ?OpenApi
	{
		return OpenApiGenerator::scan($this->getFinder(), ['version' => $this->version]);
	}

	/**
	 * Writes the api specification to the specified output file.
	 */
	public function generate(): void
	{
		$this->fileSystem->put($this->outputFile, $this->getGenerator()->toYaml());
	}
}
