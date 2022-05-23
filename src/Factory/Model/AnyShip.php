<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent\Shipbuilding;
use Lemuria\SingletonTrait;

final class AnyShip implements Ship
{
	use BuilderTrait;
	use SingletonTrait;

	public function Captain(): int {
		return 0;
	}

	public function Crew(): int {
		return 0;
	}

	public function Payload(): int {
		return 0;
	}

	public function Speed(): int {
		return 0;
	}

	public function Wood(): int {
		return 0;
	}

	public function Tare(): int {
		return 0;
	}

	public function getCraft(): Requirement {
		$talent = self::createTalent(Shipbuilding::class);
		return new Requirement($talent, 0);
	}

	public function getMaterial(): Resources {
		return new Resources();
	}
}
