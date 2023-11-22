<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Phrase;

class PhraseTest extends Base
{
	private const string PHRASE = 'TEstE Klasse Phrase Cool';

	#[Test]
	public function construct(): Phrase {
		$phrase = new Phrase(self::PHRASE);

		$this->assertInstanceOf(Phrase::class, $phrase);
		return $phrase;
	}

	#[Test]
	#[Depends('construct')]
	public function testCountable(Phrase $phrase): void {
		$this->assertSame(3, count($phrase));
	}

	#[Test]
	#[Depends('construct')]
	public function getVerb(Phrase $phrase): void {
		$this->assertSame('TESTE', $phrase->getVerb());
	}

	#[Test]
	#[Depends('construct')]
	public function getParameter(Phrase $phrase): void {
		$this->assertSame('Cool', $phrase->getParameter(-1));
		$this->assertSame('Klasse', $phrase->getParameter());
		/** @noinspection PhpRedundantOptionalArgumentInspection */
		$this->assertSame('Klasse', $phrase->getParameter(1));
		$this->assertSame('Phrase', $phrase->getParameter(2));
		$this->assertSame('Cool', $phrase->getParameter(3));
		$this->assertEmpty($phrase->getParameter(4));
	}

	#[Test]
	#[Depends('construct')]
	public function getLine(Phrase $phrase): void {
		$this->assertSame('Klasse Phrase Cool', $phrase->getLine());
		$this->assertSame('Klasse', $phrase->getLine(1, 1));
		$this->assertSame('Klasse Phrase', $phrase->getLine(last: 2));
	}

	#[Test]
	#[Depends('construct')]
	public function getLineUntil(Phrase $phrase): void {
		$this->assertSame('Klasse Phrase', $phrase->getLineUntil());
		$this->assertSame('Klasse', $phrase->getLineUntil(2));
	}

	#[Test]
	#[Depends('construct')]
	public function toStringReturnsThePhrase(Phrase $phrase): void {
		$this->assertSame(self::PHRASE, (string)$phrase);
	}
}
