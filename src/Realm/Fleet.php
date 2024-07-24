<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\TransportImpossibleMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * The fleet of a realm is the set of all available units that have no activity.
 */
class Fleet implements Reassignment
{
	use MessageTrait;

	/**
	 * @type array<string, true>
	 */
	protected const array ALLOWED_ACTIVITIES = [Learn::class => true, Teach::class => true];

	/**
	 * @var array<int, Wagoner>
	 */
	protected array $wagoner = [];

	/**
	 * @var array<int, int>
	 */
	private array $incoming = [];

	/**
	 * @var array<int, int>
	 */
	private array $outgoing = [];

	private static ?State $state = null;

	public function __construct(protected readonly Realm $realm) {
		if (!self::$state) {
			self::$state = State::getInstance();
		}
		$party = $realm->Party();
		foreach ($realm->Territory()->Central()->Residents() as $unit) {
			if ($unit->Party() === $party && $unit->IsTransporting()) {
				if ($this->isAvailable($unit) && !$unit->Vessel()) {
					$wagoner = new Wagoner($unit);
					if ($wagoner->Maximum() > 0) {
						$id                  = $unit->Id()->Id();
						$this->wagoner[$id]  = $wagoner;
						$this->incoming[$id] = $wagoner->Incoming();
						$this->outgoing[$id] = $wagoner->Outgoing();
					}
				} else {
					$this->message(TransportImpossibleMessage::class, $unit);
				}
			}
		}
		if (count($this->wagoner)) {
			arsort($this->incoming, SORT_NUMERIC);
			arsort($this->outgoing, SORT_NUMERIC);
			Lemuria::Catalog()->addReassignment($this);
		}
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$old = $oldId->Id();
			$new = $identifiable->Id()->Id();
			if (isset($this->wagoner[$old])) {
				$this->wagoner[$new] = $this->wagoner[$old];
				unset($this->wagoner[$old]);
			}
			if (isset($this->incoming[$old])) {
				$this->incoming[$new] = $this->incoming[$old];
				unset($this->incoming[$old]);
			}
			if (isset($this->outgoing[$old])) {
				$this->outgoing[$new] = $this->outgoing[$old];
				unset($this->outgoing[$old]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->wagoner[$id]);
			unset($this->incoming[$id]);
			unset($this->outgoing[$id]);
		}
	}

	public function Incoming(): int {
		$incoming = 0;
		$remove   = [];
		foreach ($this->wagoner as $id => $wagoner) {
			if ($this->isAvailable($wagoner->Unit())) {
				$incoming += $wagoner->Incoming();
			} else {
				$remove[] = $id;
			}
		}
		$this->removeWagoners($remove);
		return $incoming;
	}

	public function Outgoing(): int {
		$outgoing = 0;
		$remove   = [];
		foreach ($this->wagoner as $id => $wagoner) {
			if ($this->isAvailable($wagoner->Unit())) {
				$outgoing += $wagoner->Outgoing();
			} else {
				$remove[] = $id;
			}
		}
		$this->removeWagoners($remove);
		return $outgoing;
	}

	public function getMounts(Animal $animal): int {
		$count  = 0;
		$remove = [];
		foreach ($this->wagoner as $id => $wagoner) {
			if ($this->isAvailable($wagoner->Unit())) {
				$count += $wagoner->getMounts($animal);
			} else {
				$remove[] = $id;
			}
		}
		$this->removeWagoners($remove);
		return $count;
	}

	public function getUsedCapacity(Unit $unit): float {
		$wagoner = $this->wagoner[$unit->Id()->Id()] ?? null;
		return $wagoner ? $wagoner->UsedCapacity(): 0.0;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	public function fetch(int $weight): int {
		$fetch  = 0;
		$remove = [];
		while ($weight > 0 && !empty($this->incoming)) {
			$transport = null;
			foreach ($this->incoming as $id => $capacity) {
				$wagoner = $this->wagoner[$id];
				if ($this->isAvailable($wagoner->Unit())) {
					if ($weight <= $capacity) {
						$transport = $wagoner;
					} else {
						if (!$transport) {
							$transport = $wagoner;
						}
						break;
					}
				} else {
					$remove[] = $id;
				}
			}
			$this->removeWagoners($remove);
			if (!$transport) {
				return $fetch;
			}

			$id       = $transport->Unit()->Id()->Id();
			$capacity = $transport->Incoming();
			if ($weight <= $capacity) {
				$this->incoming[$id] = $transport->fetch($weight);
				$fetch              += $weight;
				arsort($this->incoming, SORT_NUMERIC);
				Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' fetches ' . ($weight / 100) . ' GE, ' . ($this->incoming[$id] / 100) .' GE remain.');
				return $fetch;
			}
			$fetch  += $capacity;
			$weight -= $capacity;
			$transport->fetch($capacity);
			unset($this->incoming[$id]);
			Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' fetches ' . ($capacity / 100) . ' GE.');
		}

		return $fetch;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	public function send(int $weight): int {
		$send   = 0;
		$remove = [];
		while ($weight > 0 && !empty($this->outgoing)) {
			$transport = null;
			foreach ($this->outgoing as $id => $capacity) {
				$wagoner = $this->wagoner[$id];
				if ($this->isAvailable($wagoner->Unit())) {
					if ($weight <= $capacity) {
						$transport = $wagoner;
					} else {
						if (!$transport) {
							$transport = $wagoner;
						}
						break;
					}
				} else {
					$remove[] = $id;
				}
			}
			$this->removeWagoners($remove);
			if (!$transport) {
				return $send;
			}

			$id       = $transport->Unit()->Id()->Id();
			$capacity = $transport->Outgoing();
			if ($weight <= $capacity) {
				$this->outgoing[$id] = $transport->send($weight);
				$send               += $weight;
				arsort($this->outgoing, SORT_NUMERIC);
				Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' sends ' . ($weight / 100) . ' GE, ' . ($this->outgoing[$id] / 100) .' GE remain.');
				return $send;
			}
			$send   += $capacity;
			$weight -= $capacity;
			unset($this->outgoing[$id]);
			Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' sends ' . ($weight / 100) . ' GE.');
		}

		return $send;
	}

	public function mount(Quantity $animals): Quantity {
		/** @var Animal $animal */
		$animal    = $animals->Commodity();
		$available = $this->getMounts($animal);
		$remaining = min($animals->Count(), $available);
		$mounted   = 0;
		$riders    = $this->getAvailableRiders($animal);
		while ($remaining > 0) {
			$transport = null;
			foreach ($riders as $id => $count) {
				$wagoner = $this->wagoner[$id];
				if ($remaining <= $count) {
					$transport = $wagoner;
				} else {
					if (!$transport) {
						$transport = $wagoner;
					}
					break;
				}
			}
			if (!$transport) {
				break;
			}

			$id     = $transport->Unit()->Id()->Id();
			$mounts = $transport->getMounts($animal);
			if ($remaining <= $mounts) {
				$quantity = new Quantity($animal, $remaining);
				$quantity = $transport->ride($quantity);
				$mounted += $remaining;
				Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' rides ' . $quantity . ', ' . $mounts - $remaining . ' remain.');
				break;
			}
			$quantity   = new Quantity($animal, $mounts);
			$quantity   = $transport->ride($quantity);
			$mounted   += $mounts;
			$remaining -= $mounts;
			unset($riders[$id]);
			Lemuria::Log()->debug('Wagoner ' . $transport->Unit()->Id() . ' rides ' . $quantity . '.');
		}

		return new Quantity($animal, $mounted);
	}

	protected function getAvailableRiders(Animal $animal): array {
		$riders = [];
		$remove = [];
		foreach ($this->wagoner as $id => $wagoner) {
			if ($this->isAvailable($wagoner->Unit())) {
				$mounts = $wagoner->getMounts($animal);
				if ($mounts > 0) {
					$riders[$id] = $mounts;
				}
			} else {
				$remove[] = $id;
			}
		}
		arsort($riders, SORT_NUMERIC);
		$this->removeWagoners($remove);
		return $riders;
	}

	protected function isAvailable(Unit $unit): bool {
		foreach (self::$state->getProtocol($unit)->getPlannedActivities() as $activity) {
			if (!isset(self::ALLOWED_ACTIVITIES[$activity::class])) {
				Lemuria::Log()->debug('Unit ' . $unit->Id() . ' is not available anymore for realm transport.');
				return false;
			}
		}
		return true;
	}

	private function removeWagoners(array $ids): void {
		if (!empty($ids)) {
			foreach ($ids as $id) {
				unset($this->wagoner[$id]);
				unset($this->incoming[$id]);
				unset($this->outgoing[$id]);
				$this->message(TransportImpossibleMessage::class, Unit::get(new Id($id)));
			}
		}
	}
}
