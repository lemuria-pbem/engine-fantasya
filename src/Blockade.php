<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

class Blockade extends People
{
	private int $block = 0;

	public function add(Unit $unit): static {
		if (!$this->has($unit->Id())) {
			$this->block += $unit->Size();
			return parent::add($unit);
		}
		return $this;
	}

	public function remove(Unit $unit): static {
		if ($this->has($unit->Id())) {
			$this->block -= $unit->Size();
			return parent::remove($unit);
		}
		return $this;
	}

	public function block(Unit $unit): bool {
		if ($this->block > 0) {
			$this->block -= $unit->Size();
			return true;
		}
		return false;
	}
}
