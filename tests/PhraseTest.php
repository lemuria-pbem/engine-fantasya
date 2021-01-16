<?php /** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Tests\Test;

class PhraseTest extends Test
{
	private const PHRASE = 'TESTE Klasse Phrase';

	/**
	 * @test
	 */
	public function construct(): Phrase {
		$phrase = new Phrase(self::PHRASE);

		$this->assertInstanceOf(Phrase::class, $phrase);
		return $phrase;
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function testCountable(Phrase $phrase) {
		$this->assertSame(2, count($phrase));
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function getVerb(Phrase $phrase) {
		$this->assertSame('TESTE', $phrase->getVerb());
	}

	/**
	 * @test
	 * @depends construct
	 */
	public function getParameter(Phrase $phrase) {
		$this->assertSame('Phrase', $phrase->getParameter(-1));
		$this->assertSame('Klasse', $phrase->getParameter());
		/** @noinspection PhpRedundantOptionalArgumentInspection */
		$this->assertSame('Klasse', $phrase->getParameter(1));
		$this->assertSame('Phrase', $phrase->getParameter(2));
		$this->assertEmpty($phrase->getParameter(3));
	}

	/**
	 * @depends construct
	 */
	public function testToString(Phrase $phrase) {
		$this->assertSame(self::PHRASE, (string)$phrase);
	}
}
