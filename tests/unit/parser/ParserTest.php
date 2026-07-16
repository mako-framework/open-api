<?php

namespace mako\openapi\tests\unit\parser;

use mako\openapi\parser\exceptions\ParserException;
use mako\openapi\parser\Operation;
use mako\openapi\parser\Parser;
use mako\openapi\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Yaml\Yaml;

#[Group('unit')]
class ParserTest extends TestCase
{
	/**
	 *
	 */
	public function testBasic(): void
	{
		$parser = new Parser(Yaml::parseFile(__DIR__ . '/fixtures/basic.yaml'));

		$parsed = $parser->parse();

		$this->assertCount(4, $parsed);

		foreach ($parsed as $operation) {
			$this->assertInstanceOf(Operation::class, $operation);
		}

		// 1

		$this->assertSame('/', $parsed[0]->path);
		$this->assertSame('get', $parsed[0]->method);
		$this->assertSame('app\http\controllers\RootGet', $parsed[0]->operationId);

		// 2

		$this->assertSame('/', $parsed[1]->path);
		$this->assertSame('post', $parsed[1]->method);
		$this->assertSame('app\http\controllers\RootPost', $parsed[1]->operationId);

		// 3

		$this->assertSame('/foo', $parsed[2]->path);
		$this->assertSame('get', $parsed[2]->method);
		$this->assertSame('app\http\controllers\FooGet', $parsed[2]->operationId);

		// 4

		$this->assertSame('/foo', $parsed[3]->path);
		$this->assertSame('post', $parsed[3]->method);
		$this->assertSame('app\http\controllers\FooPost', $parsed[3]->operationId);
	}

	/**
	 *
	 */
	public function testParameters(): void
	{
		$parser = new Parser(Yaml::parseFile(__DIR__ . '/fixtures/parameters.yaml'));

		$parsed = $parser->parse();

		$this->assertCount(1, $parsed);

		$this->assertSame('/articles/{articleId}/{slug}', $parsed[0]->path);
		$this->assertSame('get', $parsed[0]->method);
		$this->assertSame('app\http\controllers\ArticlesGet', $parsed[0]->operationId);

		$this->assertCount(2, $parsed[0]->pathParameters);
		$this->assertCount(1, $parsed[0]->queryParameters);
		$this->assertCount(0, $parsed[0]->cookies);
		$this->assertCount(0, $parsed[0]->headers);

		// Path parameters

		$this->assertSame('articleId', $parsed[0]->pathParameters[0]->name);
		$this->assertTrue($parsed[0]->pathParameters[0]->required);
		$this->assertSame('integer', $parsed[0]->pathParameters[0]->schema['type']);
		$this->assertSame('auto-increment', $parsed[0]->pathParameters[0]->schema['format']);

		$this->assertSame('slug', $parsed[0]->pathParameters[1]->name);
		$this->assertFalse($parsed[0]->pathParameters[1]->required);
		$this->assertSame('string', $parsed[0]->pathParameters[1]->schema['type']);

		// Query parameters

		$this->assertSame('preview', $parsed[0]->queryParameters[0]->name);
		$this->assertFalse($parsed[0]->queryParameters[0]->required);
		$this->assertSame('boolean', $parsed[0]->queryParameters[0]->schema['type']);
	}

	/**
	 *
	 */
	public function testReferences(): void
	{
		$parser = new Parser(Yaml::parseFile(__DIR__ . '/fixtures/references.yaml'));

		$parsed = $parser->parse();

		$this->assertCount(1, $parsed);

		$this->assertSame('/foobar/{id}', $parsed[0]->path);
		$this->assertSame('get', $parsed[0]->method);
		$this->assertSame('app\http\controllers\user\FoobarGet', $parsed[0]->operationId);

		$this->assertCount(1, $parsed[0]->pathParameters);
		$this->assertCount(0, $parsed[0]->queryParameters);
		$this->assertCount(0, $parsed[0]->cookies);
		$this->assertCount(0, $parsed[0]->headers);

		// Path parameters

		$this->assertSame('id', $parsed[0]->pathParameters[0]->name);
		$this->assertTrue($parsed[0]->pathParameters[0]->required);
		$this->assertSame('string', $parsed[0]->pathParameters[0]->schema['type']);
		$this->assertSame('foobar', $parsed[0]->pathParameters[0]->schema['format']);
	}

	/**
	 *
	 */
	public function testMissingPaths(): void
	{
		$this->expectException(ParserException::class);
		$this->expectExceptionMessageIs("The OpenAPI document does not contain a valid 'paths' section.");

		$parser = new Parser(Yaml::parseFile(__DIR__ . '/fixtures/missing-paths.yaml'));

		$parser->parse();
	}

	/**
	 *
	 */
	public function testMissingOperationId(): void
	{
		$this->expectException(ParserException::class);
		$this->expectExceptionMessageIs("Missing required 'operationId' for [ get:/ ].");

		$parser = new Parser(Yaml::parseFile(__DIR__ . '/fixtures/missing-operation-id.yaml'));

		$parser->parse();
	}
}
