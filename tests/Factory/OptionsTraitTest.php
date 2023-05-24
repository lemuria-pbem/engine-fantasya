<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;

use Lemuria\Tests\Base;

class OptionsTraitTest extends Base
{
	use OptionsTrait;

	#[Before]
	protected function initTestDataOptions(): void {
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

	#[Test]
	public function notExistingInt(): void {
		$name = 'I-do-not-exist';

		$this->expectExceptionInOption('int', $name);

		$this->getOption($name, 'int');
	}

	#[Test]
	public function notExistingClass(): void {
		$name = 'I-do-not-exist';

		$this->expectExceptionInOption(Phrase::class, $name);

		$this->getOption($name, Phrase::class);
	}

	#[Test]
	public function emptyStringOption(): void {
		$name = 'empty';

		$this->expectExceptionInOption('string', $name);

		$this->getOption($name, 'string');
	}

	#[Test]
	public function emptyClassOption(): void {
		$name = 'empty';

		$this->expectExceptionInOption(LemuriaException::class, $name);

		$this->getOption($name, LemuriaException::class);
	}

	#[Test]
	public function boolOption(): void {
		$this->assertTrue($this->getOption('bool', 'bool'));
	}

	#[Test]
	public function intOption(): void {
		$this->assertSame(123, $this->getOption('int', 'int'));
	}

	#[Test]
	public function notIntOption(): void {
		$name = 'string';

		$this->expectExceptionInOption('int', $name);

		$this->getOption($name, 'int');
	}

	#[Test]
	public function floatOption(): void {
		$this->assertSame(0.25, $this->getOption('float', 'float'));
	}

	#[Test]
	public function stringOption(): void {
		$this->assertSame('123', $this->getOption('string', 'string'));
	}

	#[Test]
	public function notStringOption(): void {
		$name = 'int';

		$this->expectExceptionInOption('string', $name);

		$this->getOption($name, 'string');
	}

	#[Test]
	public function phraseOption(): void {
		$this->assertSame($this->options['phrase'], $this->getOption('phrase', Phrase::class));
	}

	#[Test]
	public function notPhraseOption(): void {
		$name = 'exception';

		$this->expectExceptionInOption(Phrase::class, $name);

		$this->getOption($name, Phrase::class);
	}
}
