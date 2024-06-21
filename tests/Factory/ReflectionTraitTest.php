<?php
declare(strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Event\Administrator\ResetGatherUnits;
use PHPUnit\Framework\Attributes\Test;
use SATHub\PHPUnit\Base;

use Lemuria\Engine\Fantasya\Event\Administrator\Overcrowded;
use Lemuria\Engine\Fantasya\Event\Game\FindWallet;
use Lemuria\Engine\Fantasya\Exception\ReflectionException;
use Lemuria\Engine\Fantasya\Factory\DefaultProgress;
use Lemuria\Engine\Fantasya\Factory\ReflectionTrait;

class ReflectionTraitTest extends Base
{
	use ReflectionTrait;

	#[Test]
	public function validateAdministratorEvent(): void {
		$this->assertFalse($this->validateEventClass(Overcrowded::class));
	}

	#[Test]
	public function validateAdministratorEventWithOptions(): void {
		$this->assertTrue($this->validateEventClass(ResetGatherUnits::class));
	}

	#[Test]
	public function validateInvalidEvent(): void {
		$this->expectException(ReflectionException::class);

		$this->validateEventClass(DefaultProgress::class);
	}

	#[Test]
	public function validateGameEvent(): void {
		$this->validateGameEventClass(FindWallet::class);

		$this->pass();
	}

	#[Test]
	public function validateInvalidGameEvent(): void {
		$this->expectException(ReflectionException::class);

		$this->validateGameEventClass(Overcrowded::class);
	}
}
