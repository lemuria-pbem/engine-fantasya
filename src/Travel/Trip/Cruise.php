<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Vessel;

class Cruise extends AbstractTrip
{
	protected Vessel $vessel;

	public function __construct(Calculus $calculus) {
		parent::__construct($calculus);
		$vessel = $calculus->Unit()->Vessel();
		if (!$vessel) {
			throw new LemuriaException('Cannot cruise without a ship.');
		}
		$this->vessel   = $vessel;
		$this->movement = Movement::Ship;
	}

	public function Speed(): int {
		return (int)floor($this->vessel->Completion() * $this->vessel->Ship()->Speed());
	}

	protected function calculateCapacity(): void {
		$this->capacity = (int)floor($this->vessel->Completion() * $this->vessel->Ship()->Payload());
	}

	protected function calculateKnowledge(): void {
		$this->knowledge = $this->vessel->Ship()->Captain();
	}
}
