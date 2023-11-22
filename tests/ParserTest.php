<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Parser;
use Lemuria\Tests\Engine\Fantasya\Mock\MoveMock;

class ParserTest extends Base
{
	protected const array LINES = [
		'@MACHEN Holz',      // => @ * MACHEN Holz
		'=1 MACHEN Holz',    // => @ 0/2 MACHEN Holz
		'=2 MACHEN Holz',    // => @ 0/3 MACHEN Holz
		'+1 MACHEN Holz',    // => @ MACHEN Holz
		'+2 MACHEN Holz',    // => @ 2 MACHEN Holz
		'+1 =1 MACHEN Holz', // => @ 1/2 MACHEN Holz
		'=1 +1 MACHEN Holz', // => @ 1/2 MACHEN Holz
		'+2 =2 MACHEN Holz'  // => @ 2/3 MACHEN Holz
	];

	#[Test]
	public function construct(): Parser {
		$parser = new Parser();

		$this->assertNotNull($parser);

		return $parser;
	}

	#[Test]
	#[Depends('construct')]
	public function parse(Parser $parser): Parser {
		$this->assertSame($parser, $parser->parse(new MoveMock(self::LINES)));

		return $parser;
	}

	#[Test]
	#[Depends('parse')]
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

	#[Test]
	#[Depends('next')]
	public function hasMoreReturnsFalse(Parser $parser): void {
		$this->assertFalse($parser->hasMore());
	}
}
