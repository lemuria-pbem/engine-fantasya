<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

final readonly class BattlePlace implements \Stringable
{
	private Place $place;

	private Region $region;

	private string $name;

	public function __construct(Unit $unit) {
		$this->region = $unit->Region();
		$construction = $unit->Construction();
		if ($construction) {
			$this->place = Place::Building;
			$this->name  = 'c' . $construction->Id()->Id();
		} else {
			$vessel = $unit->Vessel();
			if ($vessel) {
				$this->place = Place::Ship;
				$this->name  = 'v' . $vessel->Id()->Id();
			} else {
				$this->place = Place::Region;
				$this->name  = 'r' . $this->region->Id()->Id();
			}
		}
	}

	public function Place(): Place {
		return $this->place;
	}

	public function Region(): Region {
		return $this->region;
	}

	public function __toString(): string {
		return $this->name;
	}
}
