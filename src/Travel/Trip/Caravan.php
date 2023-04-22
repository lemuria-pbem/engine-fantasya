<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Conveyance;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;

class Caravan extends AbstractTrip
{
	public function __construct(Calculus $calculus, Conveyance $conveyance) {
		parent::__construct($calculus, $conveyance);
		$this->movement = Movement::Walk;
	}

	public function Speed(): int {
		$animals = $this->getSpeed([Camel::class, Elephant::class, Horse::class, WarElephant::class]);
		return min($this->getUnitSpeed(), $animals);
	}

	protected function calculateCapacity(): void {
		$this->capacity = $this->calculus->payload();
		$this->addCapacity($this->conveyance->getQuantity(Camel::class));
		$this->addCapacity($this->conveyance->getQuantity(Elephant::class));
		$this->addCapacity($this->conveyance->getQuantity(Horse::class));
		$this->addCapacity($this->conveyance->getQuantity(Pegasus::class));
		$this->addCapacity($this->conveyance->getQuantity(WarElephant::class));
	}

	protected function calculateKnowledge(): void {
		$horseCamel       = $this->conveyance->Horse() + $this->conveyance->Camel();
		$horseCamel      -= $this->calculus->Unit()->Size();
		$this->knowledge  = max(0, $horseCamel);
		$this->knowledge += $this->conveyance->Pegasus() * 3;
		$this->knowledge += ($this->conveyance->Elephant() + $this->conveyance->WarElephant()) * 2;
	}

	protected function calculateWeight(): void {
		$this->weight = $this->conveyance->getPayload();
	}
}
