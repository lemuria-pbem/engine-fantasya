<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\BuildingEffect;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Constructing;
use Lemuria\SingletonTrait;

class AnyBuilding implements Building
{
	use BuilderTrait;
	use SingletonTrait;

	public function Weight(): int {
		return 0;
	}

	public function Dependency(): ?Building {
		return Building::IS_INDEPENDENT;
	}

	public function Feed(): int {
		return 0;
	}

	public function MaxSize(): int {
		return Building::IS_UNLIMITED;
	}

	public function Talent(): int {
		return 0;
	}

	public function Upkeep(): int {
		return Building::IS_FREE;
	}

	public function UsefulSize(): int {
		return Building::IS_UNLIMITED;
	}

	public function BuildingEffect(): BuildingEffect {
		return new BuildingEffect();
	}

	public function StructurePoints(): int {
		return 0;
	}

	public function getCraft(): Requirement {
		$talent = self::createTalent(Constructing::class);
		return new Requirement($talent);
	}

	public function getMaterial(): Resources {
		return new Resources();
	}

	public function correctBuilding(int $size): Building {
		return $this;
	}

	public function correctSize(int $size): int {
		return $size;
	}
}
