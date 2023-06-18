<?php
declare(strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Travel;

use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Travel\StaminaBonus;

class StaminaBonusTest extends Base
{
	#[Test]
	public function factorNegative(): void {
		$this->assertSame(0.0, StaminaBonus::factor(-3));
		$this->assertSame(0.0, StaminaBonus::factor(-2));
		$this->assertSame(0.0, StaminaBonus::factor(-1));
	}

	#[Test]
	public function factorZero(): void {
		$this->assertSame(0.0, StaminaBonus::factor(0));
	}

	#[Test]
	public function factorPositiveTill12(): void {
		$this->assertSame(0.1, StaminaBonus::factor(1));
		$this->assertSame(0.2, StaminaBonus::factor(2));
		$this->assertSame(0.3, StaminaBonus::factor(3));
		$this->assertSame(0.4, StaminaBonus::factor(4));
		$this->assertSame(0.5, StaminaBonus::factor(5));
		$this->assertSame(0.6, StaminaBonus::factor(6));
		$this->assertSame(0.68, StaminaBonus::factor(7));
		$this->assertSame(0.76, StaminaBonus::factor(8));
		$this->assertSame(0.84, StaminaBonus::factor(9));
		$this->assertSame(0.9, StaminaBonus::factor(10));
		$this->assertSame(0.96, StaminaBonus::factor(11));
		$this->assertSame(1.0, StaminaBonus::factor(12));
	}

	#[Test]
	public function factorGreaterThan12(): void {
		$this->assertSame(round(13 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(13), 3));
		$this->assertSame(round(14 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(14), 3));
		$this->assertSame(round(15 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(15), 3));
		$this->assertSame(round(16 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(16), 3));
		$this->assertSame(round(17 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(17), 3));
		$this->assertSame(round(18 ** (1 / 3.6) - 1, 3), round(StaminaBonus::factor(18), 3));
	}

	#[Test]
	public function factor52(): void {
		$this->assertSame(2.0, round(StaminaBonus::factor(52), 1));
	}
}
