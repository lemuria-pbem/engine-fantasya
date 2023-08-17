<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\FavorableWinds;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Factory\ContextTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Ports;
use Lemuria\Engine\Fantasya\Message\Region\TravelAirshipMessage;
use Lemuria\Engine\Fantasya\Message\Region\TravelVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterPortDutyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterPortDutyPaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterPortSmuggleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardCancelMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Duty;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Aquan;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Neighbours;
use Lemuria\Model\World\Direction;

trait NavigationTrait
{
	use BuilderTrait;
	use ContextTrait;

	private ?Vessel $vessel = null;

	private function navigationTalent(): int {
		$talent = 0;
		foreach ($this->vessel->Passengers() as $unit) {
			$talent += $unit->Size() * $this->context->getCalculus($unit)->knowledge(Navigation::class)->Level();
		}
		return $talent;
	}

	private function hasSufficientCrew(): bool {
		$ship       = $this->vessel->Ship();
		$passengers = $this->vessel->Passengers();
		$captain    = $passengers->Owner();
		$knowledge  = $this->context->getCalculus($captain)->knowledge($this->navigation)->Level();
		if ($knowledge < $ship->Captain()) {
			return false;
		}
		return $this->navigationTalent() >= $ship->Crew();
	}

	private function getNeighbourRegions(Region $region): Neighbours {
		$neighbours = Lemuria::World()->getNeighbours($region);
		foreach ($neighbours->getDirections() as $direction) {
			if (!$neighbours[$direction]) {
				unset($neighbours[$direction]);
			}
		}
		return $neighbours;
	}

	private function getCoastline(Neighbours $neighbours): Neighbours {
		$coastlines = new Neighbours();
		foreach ($neighbours as $direction => $region) {
			if (!($region->Landscape() instanceof Navigable)) {
				$coastlines[$direction] = $region;
			}
		}
		return $coastlines;
	}

	private function canSailTo(Region $region): bool {
		$ports = new Ports($this->vessel, $region);
		$port  = $ports->Port();
		if ($port) {
			$this->vessel->setPort($port);
			return true;
		}
		if ($ports->IsDenied()) {
			if ($this->vessel->Ship() instanceof Boat) {
				return true;
			}
			return false;
		}
		return $ports->CanLand();
	}

	private function isNavigatedByAquans(): bool {
		$passengers = $this->vessel->Passengers();
		$captain    = $passengers->Owner();
		if ($captain->Race() instanceof Aquan) {
			$points = 0;
			foreach ($passengers as $unit) {
				if ($unit->Race() instanceof Aquan) {
					$level   = $this->context->getCalculus($unit)->knowledge(Navigation::class)->Level();
					$points += $unit->Size() * $level;
				}
			}
			return $points >= $this->vessel->Ship()->Crew();
		}
		return false;
	}

	private function hasFavorableWinds(): bool {
		$effect = new FavorableWinds(State::getInstance());
		return Lemuria::Score()->find($effect->setVessel($this->vessel)) instanceof FavorableWinds;
	}

	private function moveVessel(Region $destination): void {
		$region = $this->vessel->Region();
		$region->Fleet()->remove($this->vessel);
		$destination->Fleet()->add($this->vessel);

		if ($destination->Landscape() instanceof Navigable) {
			$this->vessel->setAnchor(Direction::IN_DOCK);
			$this->vessel->setPort(null);
		} else {
			if ($this->airshipped) {
				$this->vessel->setAnchor(Direction::IN_DOCK);
				$this->vessel->setPort(null);
			} else {
				$neighbours = Lemuria::World()->getNeighbours($destination);
				$this->vessel->setAnchor($neighbours->getDirection($region));
			}
		}

		foreach ($this->vessel->Passengers() as $unit) {
			if ($unit->IsGuarding()) {
				$unit->setIsGuarding(false);
				$this->message(TravelGuardCancelMessage::class, $unit);
			}

			$region->Residents()->remove($unit);
			$destination->Residents()->add($unit);
			$this->createNavigationEffect($unit);
			$unit->Party()->Chronicle()->add($destination);
		}

		if ($this->airshipped) {
			$this->message(TravelAirshipMessage::class, $region)->p((string)$this->vessel);
		} else {
			$this->message(TravelVesselMessage::class, $region)->p((string)$this->vessel);
		}
		if ($this->vessel->Port()) {
			$this->payDutyToHarbourMaster();
		}
	}

	private function payDutyToHarbourMaster(): void {
		$port   = $this->vessel->Port();
		$master = $port->Inhabitants()->Owner();
		/** @var Duty $extension */
		$extension = $port->Extensions()->offsetGet(Duty::class);
		$rate      = $extension->Duty();
		if ($master && $rate > 0.0) {
			$party           = $master->Party();
			$diplomacy       = $party->Diplomacy();
			$masterInventory = $master->Inventory();
			$calculus        = new Calculus($master);
			$perception      = $calculus->knowledge(Perception::class)->Level();
			foreach ($this->vessel->Passengers() as $unit) {
				$duty      = [];
				$inventory = $unit->Inventory();
				foreach ($inventory as $quantity) {
					$commodity = $quantity->Commodity();
					if ($commodity instanceof Luxury) {
						$count = (int)round($rate * $quantity->Count());
						if ($count > 0) {
							$duty[] = new Quantity($commodity, $count);
						}
					}
				}
				if (!empty($duty)) {
					$passengerParty = $unit->Party();
					if ($passengerParty !== $party && !$diplomacy->has(Relation::TRADE, $unit)) {
						$calculus   = new Calculus($unit);
						$camouflage = $calculus->knowledge(Camouflage::class)->Level();
						if ($unit->IsHiding() && $camouflage > $perception) {
							$this->message(EnterPortSmuggleMessage::class, $unit);
						} else {
							foreach ($duty as $quantity) {
								$inventory->remove($quantity);
								$quantity = new Quantity($quantity->Commodity(), $quantity->Count());
								$masterInventory->add($quantity);
								$this->message(EnterPortDutyMessage::class, $unit)->i($quantity);
								$this->message(EnterPortDutyPaidMessage::class, $master)->e($unit)->i($quantity);
							}
						}
					}
				}
			}
		}
	}

	private function createNavigationEffect(Unit $unit): void {
		$effect = new TravelEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($unit))) {
			Lemuria::Score()->add($effect);
		}
	}
}
