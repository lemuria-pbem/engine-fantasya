<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Message\Region\Event\IntegrityDissolvedMessage;
use Lemuria\Engine\Fantasya\Message\Region\RealmDisconnectedMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Landmass;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Territory;
use Lemuria\Model\World\Direction;
use Lemuria\Model\World\Strategy\ShortestPath;

/**
 * The integrity of all realms is checked.
 *
 * - Central region and every other region must be governed and not guarded by foreigners only.
 * - Ring-1 regions must have a border to the center that is not blocked from any side.
 * - Ring-2 regions must have at least one Ring-1 neighbour that is connected to central region, must have a border to
 *   at least one Ring-1 neighbour that is not blocked from any side, and must be connected with a completed road.
 */
final class Integrity extends AbstractEvent
{
	use RealmTrait;

	private Realm $realm;

	private Party $governor;

	private Territory $territory;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		$world = Lemuria::World();
		foreach (Realm::all() as $realm) {
			// Sort regions by ring.
			$this->realm     = $realm;
			$this->governor  = $realm->Party();
			$this->territory = $realm->Territory();
			$central         = $this->territory->Central();
			$ring            = [];
			foreach ($this->territory as $region) {
				if ($region !== $central) {
					$r = $world->getDistance($central, $region);
					if (!isset($ring[$r])) {
						$ring[$r] = new Landmass();
					}
					$ring[$r]->add($region);
				}
			}
			$r = $this->validateRings($ring);

			// Check central region.
			$lostRegions = new Landmass();
			$governor = $this->getGovernor($central);
			if ($governor !== $this->governor) {
				$this->dissolveRealm($central, $governor);
				continue;
			}
			if ($r < 1) {
				continue;
			}

			$centralBlock = $this->getBlockedDirections($central);
			$neighbours   = $world->getNeighbours($central);
			foreach ($ring[1] as $region) {
				if ($this->getGovernor($region) === $this->governor) {
					$direction = $neighbours->getDirection($region);
					if (in_array($direction, $centralBlock)) {
						$lostRegions->add($region);
					} else {
						$blocked = $this->getBlockedDirections($region);
						if (in_array($direction->getOpposite(), $blocked)) {
							$lostRegions->add($region);
						}
					}
				} else {
					$lostRegions->add($region);
				}
			}
			if ($r < 2) {
				continue;
			}

			foreach ($ring[2] as $region) {
				if ($this->getGovernor($region) === $this->governor) {
					$ways  = $world->findPath($central, $region, ShortestPath::class)->getAll();
					$valid = $ways->count();
					foreach ($ways as $way) {
						/** @var Region $neighbour */
						$neighbour = $way[1];
						if ($lostRegions->has($neighbour->Id())) {
							$valid--;
						} else {
							$neighbours = $world->getNeighbours($neighbour);
							$direction  = $neighbours->getDirection($region);
							$blocked    = $this->getBlockedDirections($neighbour);
							if (in_array($direction, $blocked)) {
								$valid--;
							} else {
								$blocked = $this->getBlockedDirections($region);
								if (in_array($direction->getOpposite(), $blocked)) {
									$valid--;
								} elseif (!$this->hasCompletedRoad($way)) {
									$valid--;
								}
							}
						}
					}
					if ($valid <= 0) {
						$lostRegions->add($region);
					}
				} else {
					$lostRegions->add($region);
				}
			}

			if ($lostRegions->isEmpty()) {
				Lemuria::Log()->debug('Realm ' . $this->realm . ' is still intact.');
			} else {
				$this->removeLostRegions($lostRegions);
			}
		}
	}

	/**
	 * @var array<int, Landmass> $rings
	 */
	private function validateRings(array $rings): int {
		$distances = array_keys($rings);
		$n         = count($distances);
		$r         = 0;
		for ($i = 0; $i < $n; $i++) {
			$r++;
			if ($distances[$i] !== $r) {
				throw new LemuriaException('Ring ' . $r . ' is missing in realm ' . $this->realm . '.');
			}
		}
		if ($r > 2) {
			throw new LemuriaException('Realm ' . $this->realm . ': Rings > 2 are not supported yet.');
		}
		return $r;
	}

	private function getGovernor(Region $region): ?Party {
		$intelligence = new Intelligence($region);
		$government   = $intelligence->getGovernment();
		$governor     = $government?->Inhabitants()->Owner()?->Party();
		if ($governor) {
			return $governor;
		}
		$guards = $intelligence->getGuards();
		if ($guards->isEmpty() || $guards->has($this->governor->Id())) {
			return $this->governor;
		}
		return $guards->getFirst()?->Party();
	}

	/**
	 * @return array<Direction>
	 */
	private function getBlockedDirections(Region $region): array {
		$blocked      = [];
		$intelligence = new Intelligence($region);
		foreach ($intelligence->getGuards() as $unit) {
			$direction = $unit->GuardDirection();
			if ($direction !== Direction::None) {
				$blocked[$direction->value] = $direction;
			}
		}
		return array_values($blocked);
	}

	private function dissolveRealm(Region $central, Party $governor): void {
		$this->governor->Possessions()->remove($this->realm);
		Lemuria::Catalog()->remove($this->realm);
		$this->message(IntegrityDissolvedMessage::class, $central)->p($this->realm->Name())->e($governor);
		Lemuria::Log()->debug('Realm ' . $this->realm . ' has been dissolved.');
	}

	private function removeLostRegions(Landmass $regions): void {
		foreach ($regions as $region) {
			$this->territory->remove($region);
			$this->message(RealmDisconnectedMessage::class, $region)->p($this->realm->Name())->e($this->governor);
			Lemuria::Log()->debug('Realm ' . $this->realm . ' lost region ' . $region . '.');
		}
	}
}
