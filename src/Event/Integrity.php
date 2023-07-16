<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Message\Region\RealmDisconnectedMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Realm;

/**
 * The integrity of all realms is checked:
 *
 * - A region can lose its association to a realm if the connecting road is destroyed.
 */
final class Integrity extends AbstractEvent
{
	use RealmTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Realm::all() as $realm) {
			$lostRegions = [];
			$territory = $realm->Territory();
			foreach ($territory as $region) {
				if (!$this->isValidNeighbour($realm, $region)) {
					$lostRegions[] = $region;
				}
			}
			foreach ($lostRegions as $region) {
				$territory->remove($region);
				$this->message(RealmDisconnectedMessage::class, $region)->p($realm->Name())->e($realm->Party());
			}
		}
	}
}
