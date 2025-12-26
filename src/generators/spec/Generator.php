<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\generators\spec;

use mako\file\FileSystem;
use mako\openapi\generators\spec\processors\OperationId;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator as OpenApiGenerator;
use OpenApi\Processors\OperationId as DefaultOperationId;
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
		protected array $directory,
		protected ?array $exclude = null,
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
	protected function getSpec(): ?OpenApi
	{
		$generator = (new OpenApiGenerator)->setVersion($this->version);

		$generator->getProcessorPipeline()
		->remove(DefaultOperationId::class)
		->add(new OperationId);

		return $generator->generate($this->getFinder());
	}

	/**
	 * Writes the api specification to the specified output file.
	 */
	public function generate(): void
	{
		$this->fileSystem->put($this->outputFile, $this->getSpec()->toYaml());
	}
}
