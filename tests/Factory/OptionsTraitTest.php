<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Tests\Test;

class OptionsTraitTest extends Test
{
	use OptionsTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->options['empty']     = null;
		$this->options['bool']      = true;
		$this->options['int']       = 123;
		$this->options['float']     = 0.25;
		$this->options['string']    = '123';
		$this->options['phrase']    = new Phrase('TEST');
		$this->options['exception'] = new LemuriaException();
	}

	protected function expectExceptionInOption(string $type, string $name): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Expected ' . $type . ' option "' . $name . '".');
	}

	/**
	 * @test
	 */
	public function notExistingInt(): void {
		$name = 'I-do-not-exist';

		$this->expectExceptionInOption('int', $name);

		$this->getOption($name, 'int');
	}

	/**
	 * @test
	 */
	public function notExistingClass(): void {
		$name = 'I-do-not-exist';

		$this->expectExceptionInOption(Phrase::class, $name);

		$this->getOption($name, Phrase::class);
	}

	/**
	 * @test
	 */
	public function emptyStringOption(): void {
		$name = 'empty';

		$this->expectExceptionInOption('string', $name);

		$this->getOption($name, 'string');
	}

	/**
	 * @test
	 */
	public function emptyClassOption(): void {
		$name = 'empty';

		$this->expectExceptionInOption(LemuriaException::class, $name);

		$this->getOption($name, LemuriaException::class);
	}

	/**
	 * @test
	 */
	public function boolOption(): void {
		$this->assertTrue($this->getOption('bool', 'bool'));
	}

	/**
	 * @test
	 */
	public function intOption(): void {
		$this->assertSame(123, $this->getOption('int', 'int'));
	}

	/**
	 * @test
	 */
	public function notIntOption(): void {
		$name = 'string';

		$this->expectExceptionInOption('int', $name);

		$this->getOption($name, 'int');
	}

	/**
	 * @test
	 */
	public function floatOption(): void {
		$this->assertSame(0.25, $this->getOption('float', 'float'));
	}

	/**
	 * @test
	 */
	public function stringOption(): void {
		$this->assertSame('123', $this->getOption('string', 'string'));
	}

	/**
	 * @test
	 */
	public function notStringOption(): void {
		$name = 'int';

		$this->expectExceptionInOption('string', $name);

		$this->getOption($name, 'string');
	}

	/**
	 * @test
	 */
	public function phraseOption(): void {
		$this->assertSame($this->options['phrase'], $this->getOption('phrase', Phrase::class));
	}

	/**
	 * @test
	 */
	public function notPhraseOption(): void {
		$name = 'exception';

		$this->expectExceptionInOption(Phrase::class, $name);

		$this->getOption($name, Phrase::class);
	}
}
