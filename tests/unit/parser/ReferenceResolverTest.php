<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\openapi\tests\unit\parser;

use mako\openapi\parser\exceptions\ParserException;
use mako\openapi\parser\ReferenceResolver;
use mako\openapi\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Yaml\Yaml;

#[Group('unit')]
class ReferenceResolverTest extends TestCase
{
	/**
	 *
	 */
	public function testResolveReference(): void
	{
		$referenceResolver = new ReferenceResolver(Yaml::parseFile(__DIR__ . '/fixtures/references.yaml'));

		$resolved = $referenceResolver->resolve('#/components/schemas/IdSchema');

		$this->assertSame('string', $resolved['type']);
		$this->assertSame('foobar', $resolved['format']);
	}

	/**
	 *
	 */
	public function testResolveNestedReference(): void
	{
		$referenceResolver = new ReferenceResolver(Yaml::parseFile(__DIR__ . '/fixtures/references.yaml'));

		$resolved = $referenceResolver->resolve('#/components/parameters/IdParam');

		$this->assertSame('id', $resolved['name']);
		$this->assertSame('path', $resolved['in']);
		$this->assertTrue($resolved['required']);
		$this->assertSame('string', $resolved['schema']['type']);
		$this->assertSame('foobar', $resolved['schema']['format']);
	}

	/**
	 *
	 */
	public function testInvalidReference(): void
	{
		$this->expectException(ParserException::class);
		$this->expectExceptionMessageIs('Invalid reference [ #/components/schemas/InvalidSchema ]. The reference does not exist.');

		$referenceResolver = new ReferenceResolver(Yaml::parseFile(__DIR__ . '/fixtures/references.yaml'));

		$referenceResolver->resolve('#/components/schemas/InvalidSchema');
	}

	/**
	 *
	 */
	public function testCircularReference(): void
	{
		$this->expectException(ParserException::class);
		$this->expectExceptionMessageIs('Invalid reference [ #/components/schemas/CircularRefSchema ]. Circular reference detected.');

		$referenceResolver = new ReferenceResolver(Yaml::parseFile(__DIR__ . '/fixtures/invalid-references.yaml'));

		$referenceResolver->resolve('#/components/parameters/CircularRefParam');
	}

	/**
	 *
	 */
	public function testNonLocalReference(): void
	{
		$this->expectException(ParserException::class);
		$this->expectExceptionMessageIs('Invalid reference [ ./external.yaml#/components/schemas/IdSchema ]. Only local references supported.');

		$referenceResolver = new ReferenceResolver(Yaml::parseFile(__DIR__ . '/fixtures/references.yaml'));

		$referenceResolver->resolve('./external.yaml#/components/schemas/IdSchema');
	}
}
