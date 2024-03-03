<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Realm\Allotment;
use Lemuria\Engine\Fantasya\Realm\Distributor;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\World\RoadTrait;

trait RealmTrait
{
	use RoadTrait;

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
		$allotment = new Allotment($command->Unit()->Region()->Realm(), $this->context);
		return $allotment->setThreshold($this->getImplicitThreshold());
	}

	protected function createDistributor(Merchant $merchant): Distributor {
		return new Distributor($merchant, $this->context);
	}

	protected function calculateInfrastructure(Region $region): int {
		$realm = $region->Realm();
		if ($realm) {
			$territory       = $realm->Territory();
			$structurePoints = [];
			foreach ($territory as $realmRegion) {
				$id                   = $realmRegion->Id()->Id();
				$structurePoints[$id] = $this->context->getIntelligence($realmRegion)->getInfrastructure();
			}
			$infrastructure = array_sum($structurePoints);
			if ($infrastructure <= 0 || $region === $territory->Central()) {
				return $infrastructure;
			}
			$average = $infrastructure / count($structurePoints);
			return (int)round(max($average, $structurePoints[$region->Id()->Id()]));
		}
		return $this->context->getIntelligence($region)->getInfrastructure();
	}

	protected function getImplicitThreshold(): int|float|null {
		return null;
	}

	private function isValidNeighbour(Realm $realm, Region $region): bool {
		$central  = $realm->Territory()->Central();
		$distance = Lemuria::World()->getDistance($central, $region);
		return match ($distance) {
			2       => $this->hasCompletedRoadBetween($central, $region),
			default => $distance < 2
		};
	}
}
