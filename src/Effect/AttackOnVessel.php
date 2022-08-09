<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Vessel;

final class AttackOnVessel extends AbstractUnitEffect
{
	private Vessel $vessel;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function Vessel(): Vessel {
		return $this->vessel;
	}

	public function setVessel(Vessel $vessel): AttackOnVessel {
		$this->vessel = $vessel;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
