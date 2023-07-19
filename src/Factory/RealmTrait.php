<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Realm\Allotment;
use Lemuria\Engine\Fantasya\Realm\Distributor;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;

trait RealmTrait
{
	protected function isRealmCommand(UnitCommand $command): bool {
		if (!$command->canBeCentralized()) {
			return false;
		}
		return $this->isRunCentrally($command);
	}

	protected function isRunCentrally(UnitCommand $command): bool {
		$unit   = $command->Unit();
		$region = $unit->Region();
		$realm  = $region->Realm();
		if (!$realm || $unit->Party() !== $realm->Party()) {
			return false;
		}
		return $region === $realm->Territory()->Central();
	}

	protected function createAllotment(UnitCommand $command): Allotment {
		return new Allotment($command->Unit()->Region()->Realm());
	}

	protected function createDistributor(UnitCommand $command): Distributor {
		return new Distributor($command->Unit()->Region()->Realm());
	}

	private function isValidNeighbour(Realm $realm, Region $region): bool {
		$central  = $realm->Territory()->Central();
		$distance = Lemuria::World()->getDistance($central, $region);
		return match ($distance) {
			2       => $central->hasRoadTo($region),
			default => $distance < 2
		};
	}
}
