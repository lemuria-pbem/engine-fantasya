<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

final class GriffinAttack extends AbstractRegionEffect
{
	private ?Unit $griffins = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function Griffins(): ?Unit {
		return $this->griffins;
	}

	public function setGriffins(Unit $griffins): GriffinAttack {
		$this->griffins = $griffins;
		return $this;
	}

	protected function run(): void {
		if ($this->griffins) {
			$this->griffins->Region()->Residents()->remove($this->griffins);
			$this->griffins->Party()->People()->remove($this->griffins);
			Lemuria::Catalog()->reassign($this->griffins);
			Lemuria::Catalog()->remove($this->griffins);
		}
		Lemuria::Score()->remove($this);
	}
}
