<?php
declare (strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Turn\Option;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Exception\OptionException;
use Lemuria\Engine\Fantasya\Turn\Option\ThrowOption;

class ThrowOptionTest extends Base
{
	#[Test]
	public function construct(): ThrowOption {
		$option = new ThrowOption();

		$this->assertNotNull($option);

		return $option;
	}

	#[Test]
	public function constructNone(): ThrowOption {
		$option = new ThrowOption('NONE');

		$this->assertNotNull($option);

		return $option;
	}

	#[Test]
	public function constructInvalidKey(): void {
		$this->expectException(OptionException::class);

		new ThrowOption('INVALID_KEY');
	}

	#[Test]
	public function constructInvalidOperator(): void {
		$this->expectException(OptionException::class);

		new ThrowOption('EVALUATE*ADD');
	}

	#[Test]
	#[Depends('construct')]
	public function offsetExists(ThrowOption $option): void {
		$this->assertTrue(isset($option[ThrowOption::NONE]));
		$this->assertTrue(isset($option[ThrowOption::EVALUATE]));
		$this->assertTrue(isset($option[ThrowOption::ADD]));
		$this->assertTrue(isset($option[ThrowOption::SUBSTITUTE]));
		$this->assertTrue(isset($option[ThrowOption::ANY]));
		$this->assertTrue(isset($option[12345]));
	}

	#[Test]
	#[Depends('constructNone')]
	public function offsetExistsNone(ThrowOption $option): void {
		$this->assertTrue(isset($option[ThrowOption::ANY]));
	}

	#[Test]
	#[Depends('construct')]
	public function offsetGet(ThrowOption $option): void {
		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertTrue(isset($option[ThrowOption::EVALUATE]));
		$this->assertTrue(isset($option[ThrowOption::ADD]));
		$this->assertTrue(isset($option[ThrowOption::SUBSTITUTE]));
		$this->assertTrue($option[ThrowOption::ANY]);
		$this->assertTrue($option[12345]);
	}

	#[Test]
	#[Depends('constructNone')]
	public function offsetGetNone(ThrowOption $option): ThrowOption {
		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertFalse($option[ThrowOption::EVALUATE]);
		$this->assertFalse($option[ThrowOption::ADD]);
		$this->assertFalse($option[ThrowOption::SUBSTITUTE]);
		$this->assertFalse($option[ThrowOption::ANY]);
		$this->assertFalse($option[12345]);

		return $option;
	}

	#[Test]
	#[Depends('offsetGetNone')]
	public function offsetSet(ThrowOption $option): ThrowOption {
		$option[ThrowOption::ADD] = true;

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertFalse($option[ThrowOption::EVALUATE]);
		$this->assertTrue($option[ThrowOption::ADD]);
		$this->assertFalse($option[ThrowOption::SUBSTITUTE]);
		$this->assertTrue($option[ThrowOption::ANY]);
		$this->assertFalse($option[1 + 4]);

		return $option;
	}

	#[Test]
	#[Depends('offsetSet')]
	public function offsetUnset(ThrowOption $option): void {
		unset($option[ThrowOption::ADD]);

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertFalse($option[ThrowOption::EVALUATE]);
		$this->assertFalse($option[ThrowOption::ADD]);
		$this->assertFalse($option[ThrowOption::SUBSTITUTE]);
		$this->assertFalse($option[ThrowOption::ANY]);
		$this->assertFalse($option[12345]);
	}

	#[Test]
	public function __constructPlus() {
		$option = new ThrowOption('EVALUATE+ADD');

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertTrue($option[ThrowOption::EVALUATE]);
		$this->assertTrue($option[ThrowOption::ADD]);
		$this->assertFalse($option[ThrowOption::SUBSTITUTE]);
		$this->assertTrue($option[ThrowOption::ANY]);
	}

	#[Test]
	public function __constructMinus() {
		$option = new ThrowOption('ANY-ADD');

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertTrue($option[ThrowOption::EVALUATE]);
		$this->assertFalse($option[ThrowOption::ADD]);
		$this->assertTrue($option[ThrowOption::SUBSTITUTE]);
		$this->assertTrue($option[ThrowOption::ANY]);
	}

	#[Test]
	public function __constructOr() {
		$option = new ThrowOption('EVALUATE|ADD');

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertTrue($option[ThrowOption::EVALUATE]);
		$this->assertTrue($option[ThrowOption::ADD]);
		$this->assertFalse($option[ThrowOption::SUBSTITUTE]);
		$this->assertTrue($option[ThrowOption::ANY]);
	}

	#[Test]
	public function __constructMultipleOperator() {
		$option = new ThrowOption('EVALUATE|ADD+SUBSTITUTE-ADD');

		$this->assertFalse($option[ThrowOption::NONE]);
		$this->assertTrue($option[ThrowOption::EVALUATE]);
		$this->assertFalse($option[ThrowOption::ADD]);
		$this->assertTrue($option[ThrowOption::SUBSTITUTE]);
		$this->assertTrue($option[ThrowOption::ANY]);
	}
}
