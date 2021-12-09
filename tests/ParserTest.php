<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Parser;
use Lemuria\Tests\Engine\Fantasya\Mock\MoveMock;
use Lemuria\Tests\Test;

class ParserTest extends Test
{
	protected const LINES = [
		'@MACHEN Holz',      // => @ * MACHEN Holz
		'=1 MACHEN Holz',    // => @ 0/2 MACHEN Holz
		'=2 MACHEN Holz',    // => @ 0/3 MACHEN Holz
		'+1 MACHEN Holz',    // => @ MACHEN Holz
		'+2 MACHEN Holz',    // => @ 2 MACHEN Holz
		'+1 =1 MACHEN Holz', // => @ 1/2 MACHEN Holz
		'=1 +1 MACHEN Holz', // => @ 1/2 MACHEN Holz
		'+2 =2 MACHEN Holz'  // => @ 2/3 MACHEN Holz
	];

	/**
	 * @test
	 */
	public function construct(): Parser {
		$parser = new Parser();

		$this->assertNotNull($parser);

		return $parser;
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function parse(Parser $parser): Parser {
		$this->assertSame($parser, $parser->parse(new MoveMock(self::LINES)));

		return $parser;
	}

	/**
	 * @test
	 * @depends parse
	 */
	public function next(Parser $parser): Parser {
		$this->assertSame('@ * MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 0/2 MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 0/3 MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 2 MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 1/2 MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 1/2 MACHEN Holz', (string)$parser->next());
		$this->assertSame('@ 2/3 MACHEN Holz', (string)$parser->next());

		return $parser;
	}

	public function hasMoreReturnsFalse(Parser $parser) {
		$this->assertFalse($parser->hasMore());
	}
}
