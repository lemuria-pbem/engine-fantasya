<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Effect\PotionReserve;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Quantity;

abstract class AbstractConstructionApply extends AbstractApply
{
	protected ?PotionReserve $effect = null;

	private bool $isNew = false;

	public function CanApply(): bool {
		$effect = $this->getEffect();
		return $this->isNew || !$effect->IsFresh();
	}

	public function apply(): int {
		$potion   = $this->apply->Potion();
		$count    = $this->calculateAmount();
		$quantity = new Quantity($potion, $count);
		$this->getEffect()->addPotion($quantity, $potion->Weeks());
		return $count;
	}

	abstract protected function calculateAmount(): int;

	protected function getEffect(): PotionReserve {
		if (!$this->effect) {
			$this->effect = new PotionReserve(State::getInstance());
			$this->effect->setConstruction($this->apply->Unit()->Construction());
			$existing = Lemuria::Score()->find($this->effect);
			if ($existing instanceof PotionReserve) {
				$this->effect = $existing;
			} else {
				$this->isNew = true;
				Lemuria::Score()->add($this->effect);
			}
		}
		return $this->effect;
	}
}
