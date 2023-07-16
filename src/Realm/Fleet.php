<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Unit;

/**
 * The fleet of a realm is the set of all available units that have no activity.
 */
class Fleet
{
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
		foreach ($realm->Territory()->Central()->Residents() as $unit) {
			if ($this->isAvailable($unit)) {
				$id                  = $unit->Id()->Id();
				$wagoner             = new Wagoner($unit);
				$this->wagoner[$id]  = $wagoner;
				$this->incoming[$id] = $wagoner->Incoming();
				$this->outgoing[$id] = $wagoner->Outgoing();
			}
		}
		arsort($this->incoming);
		arsort($this->outgoing);
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

	/**
	 * @noinspection DuplicatedCode
	 */
	public function fetch(int $weight): int {
		$fetch  = 0;
		$remove = [];
		while ($fetch < $weight && !empty($this->incoming)) {
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
				arsort($this->incoming);
				return $fetch;
			}
			$part    = $weight - $capacity;
			$fetch  += $part;
			$weight -= $part;
			unset($this->incoming[$id]);
		}

		throw new LemuriaException(); // We should never get here.
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	public function send(int $weight): int {
		$send   = 0;
		$remove = [];
		while ($send < $weight && !empty($this->outgoing)) {
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
				arsort($this->outgoing);
				return $send;
			}
			$part    = $weight - $capacity;
			$send   += $part;
			$weight -= $part;
			unset($this->outgoing[$id]);
		}

		throw new LemuriaException(); // We should never get here.
	}

	protected function isAvailable(Unit $unit): bool {
		if (self::$state->getProtocol($unit)->hasActivity()) {
			Lemuria::Log()->debug('Unit ' . $unit->Id() . ' is not available anymore for realm transport.');
			return false;
		}
		return true;
	}

	private function removeWagoners(array $ids): void {
		if (!empty($ids)) {
			foreach ($ids as $id) {
				unset($this->wagoner[$id]);
				unset($this->incoming[$id]);
				unset($this->outgoing[$id]);
			}
		}
	}
}
