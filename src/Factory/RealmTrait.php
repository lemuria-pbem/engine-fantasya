<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Allotment;
use Lemuria\Engine\Fantasya\Command\UnitCommand;

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
}
