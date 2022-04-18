<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Building\Quay;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

trait PortsTrait
{
	use BuilderTrait;

	/**
	 * @var Construction[]
	 */
	protected array $friendly = [];

	/**
	 * @var Construction[]
	 */
	protected array $allied = [];

	/**
	 * @var Construction[]
	 */
	protected array $unmaintained = [];

	/**
	 * @var Construction[]
	 */
	protected array $unguarded = [];

	/**
	 * @var Construction[]
	 */
	protected array $foreign = [];

	protected readonly Unit $unit;

	protected readonly Party $party;

	protected readonly Region $region;

	/**
	 * @var array(int=>int)
	 */
	protected array $used = [];

	protected function init(Unit $unit, Region $region): void {
		$this->unit   = $unit;
		$this->party  = $unit->Party();
		$this->region = $region;
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() instanceof Port) {
				$this->add($construction);
			}
		}
		foreach ($region->Fleet() as $vessel /* @var Vessel $vessel */) {
			$port = $vessel->Port()?->Id()->Id();
			if ($port) {
				if (!isset($this->used[$port])) {
					$this->used[$port] = $vessel->Ship()->Captain();
				} else {
					$this->used[$port] += $vessel->Ship()->Captain();
				}
			}
		}
	}

	protected function add(Construction $port): void {
		$master = $port->Inhabitants()->Owner();
		if (!$master || $this->isUnmaintained($port)) {
			$this->unmaintained[] = $port;
		} else {
			$party = $master->Party();
			if ($party === $this->party) {
				$this->friendly[] = $port;
			} else {
				$isGuarded = false;
				if (!State::getInstance()->getTurnOptions()->IsSimulation()) {
					foreach ($port->Inhabitants() as $unit /* @var Unit $unit */) {
						if ($unit->IsGuarding()) {
							$isGuarded = true;
							break;
						}
					}
				}
				if ($isGuarded) {
					if ($party->Diplomacy()->has(Relation::GUARD, $this->unit)) {
						$this->allied[] = $port;
					} else {
						$this->foreign[] = $port;
					}
				} else {
					$this->unguarded[] = $port;
				}
			}
		}
	}

	protected function isUnmaintained(Construction $port): bool {
		$effect = new Unmaintained(State::getInstance());
		return Lemuria::Score()->find($effect->setConstruction($port)) instanceof Unmaintained;
	}

	#[Pure] protected function hasSpace(Construction $port, int $size): bool {
		$id   = $port->Id()->Id();
		$free = $port->Size();
		if (isset($this->used[$id])) {
			$free -= $this->used[$id];
		}
		return $free >= $size;
	}

	protected function canBeSailedTo(Ship $ship): bool {
		$landscape = $this->region->Landscape();
		if ($landscape instanceof Plain || $landscape instanceof Ocean) {
			return true;
		}
		if ($ship instanceof Longboat || $ship instanceof Dragonship) {
			$calculus = new Calculus($this->unit);
			$quay     = self::createBuilding(Quay::class);
			return $calculus->canEnter($this->region, $quay);
		}
		return false;
	}
}
