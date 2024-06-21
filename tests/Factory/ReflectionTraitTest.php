<?php
declare(strict_types = 1);
namespace Lemuria\Tests\Engine\Fantasya\Factory;

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
		$this->validateEventClass(Overcrowded::class);

		$this->pass();
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
