<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Conveyance;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Pegasus;

class Flight extends AbstractTrip
{
	public function __construct(Calculus $calculus, Conveyance $conveyance) {
		parent::__construct($calculus, $conveyance);
		$this->movement = Movement::Fly;
	}

	public function Speed(): int {
		return $this->getSpeed([Pegasus::class, Griffin::class]);
	}

	protected function calculateCapacity(): void {
		$this->addCapacity($this->conveyance->getQuantity(Pegasus::class));
		$this->addCapacity($this->conveyance->getQuantity(Griffin::class));
	}

	protected function calculateKnowledge(): void {
		$this->knowledge  = 0;
		$this->knowledge += $this->conveyance->Pegasus() * 3;
		$this->knowledge += $this->conveyance->Griffin() * 6;
	}

	protected function calculateWeight(): void {
		parent::calculateWeight();
		$this->removeWeightOf(Pegasus::class);
		$this->removeWeightOf(Griffin::class);
	}
}
