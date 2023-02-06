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

class Ride extends AbstractTrip
{
	public function __construct(Calculus $calculus, Conveyance $conveyance) {
		parent::__construct($calculus, $conveyance);
		$this->movement = Movement::Ride;
	}

	public function Speed(): int {
		return $this->getSpeed([Camel::class, Elephant::class, Horse::class, WarElephant::class]);
	}

	protected function calculateCapacity(): void {
		$this->addCapacity($this->conveyance->getQuantity(Camel::class));
		$this->addCapacity($this->conveyance->getQuantity(Elephant::class));
		$this->addCapacity($this->conveyance->getQuantity(Horse::class));
		$this->addCapacity($this->conveyance->getQuantity(Pegasus::class));
		$this->addCapacity($this->conveyance->getQuantity(WarElephant::class));
	}

	protected function calculateKnowledge(): void {
		$this->knowledge  = 0;
		$this->knowledge += $this->conveyance->Pegasus() * 3;
		$this->knowledge += ($this->conveyance->Elephant() + $this->conveyance->WarElephant()) * 2;
		$this->knowledge += $this->conveyance->Horse() + $this->conveyance->Camel();
	}

	protected function calculateWeight(): void {
		$unit          = $this->calculus->Unit();
		$this->weight  = $unit->Size() * $unit->Race()->Weight();
		$this->weight += $this->conveyance->getPayload();
	}
}
