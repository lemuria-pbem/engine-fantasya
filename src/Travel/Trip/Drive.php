<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Conveyance;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Talent\Riding;

class Drive extends AbstractTrip
{
	private int $horses;

	private int $need;

	private int $difference;

	private ?int $canRide = null;

	public function __construct(Calculus $calculus, Conveyance $conveyance) {
		$this->horses     = $conveyance->Horse();
		$this->need       = ($conveyance->Catapult() + $conveyance->Carriage()) * 2;
		$this->difference = $this->horses - $this->need;
		parent::__construct($calculus, $conveyance);
		$this->movement = Movement::Drive;

		if ($this->canRide() && $this->Weight() > $this->Capacity()) {
			$this->driversMustWalk();
		}
	}

	public function Speed(): int {
		if ($this->canRide()) {
			$transports = [Camel::class, Carriage::class, Catapult::class, Elephant::class, Pegasus::class, WarElephant::class];
			if ($this->difference > 0) {
				$transports[] = Horse::class;
			}
			return $this->getSpeed($transports);
		}
		return $this->getUnitSpeed();
	}

	protected function calculateCapacity(): void {
		$this->addCapacity($this->conveyance->getQuantity(Camel::class));
		$this->addCapacity($this->conveyance->getQuantity(Carriage::class));
		$this->addCapacity($this->conveyance->getQuantity(Catapult::class));
		$this->addCapacity($this->conveyance->getQuantity(Elephant::class));
		$this->addCapacity($this->conveyance->getQuantity(Pegasus::class));
		$this->addCapacity($this->conveyance->getQuantity(WarElephant::class));

		if ($this->difference > 0) {
			$this->addCapacity(new Quantity(self::createCommodity(Horse::class), $this->difference));
		}
		if (!$this->canRide()) {
			$unit = $this->calculus->Unit();
			$size = $unit->Size();
			$race = $unit->Race();
			if ($this->difference < 0 && $race instanceof Troll) {
				$this->difference += $size;
				if ($this->difference > 0) {
					$size             = $this->difference;
					$this->difference = 0;
				} else {
					$size = 0;
				}
			}
			if ($size > 0) {
				$this->capacity += $this->calculus->payload($size);
			}
		}
	}

	protected function calculateKnowledge(): void {
		$rideKnowledge = $this->canRide();
		if ($rideKnowledge) {
			$this->knowledge = $rideKnowledge;
		} else {
			$this->knowledge  = $this->conveyance->Pegasus() * 2;
			$this->knowledge += $this->conveyance->Elephant() * 2;
			$this->knowledge += $this->conveyance->WarElephant() * 2;
			$animals          = $this->conveyance->Horse() + $this->conveyance->Camel();
			$free             = $this->calculus->Unit()->Size();
			$this->knowledge += max(0, $animals - $free);
		}
	}

	protected function calculateWeight(): void {
		$this->weight = $this->conveyance->getPayload();
		if ($this->canRide) {
			$unit          = $this->calculus->Unit();
			$this->weight += $unit->Size() * $unit->Race()->Weight();
		}
	}

	protected function canRide(): int {
		if ($this->canRide === null) {
			$this->canRide = $this->calculateCanRide();
		}
		return $this->canRide;
	}

	protected function calculateCanRide(): int {
		if ($this->difference < 0) {
			return 0;
		}

		$knowledge = ($this->conveyance->Catapult() + $this->conveyance->Carriage()) * 2;
		$knowledge += $this->conveyance->Pegasus() * 2;
		$knowledge += $this->conveyance->Elephant() * 2;
		$knowledge += $this->conveyance->WarElephant() * 2;
		$knowledge += $this->conveyance->Camel();
		$knowledge += $this->difference;

		$talent = $this->calculus->Unit()->Size() * $this->calculus->knowledge(Riding::class)->Level();
		if ($talent >= $knowledge) {
			return $knowledge;
		}
		return 0;
	}

	protected function driversMustWalk(): void {
		$this->canRide  = 0;
		$this->capacity = 0;
		$this->calculateCapacity();
		$this->calculateKnowledge();
		$this->calculateWeight();
		$this->movement = Movement::Walk;
	}
}
