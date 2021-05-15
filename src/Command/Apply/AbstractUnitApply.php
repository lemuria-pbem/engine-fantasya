<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

abstract class AbstractUnitApply extends AbstractApply
{
	protected ?PotionEffect $effect = null;

	public function CanApply(): bool {
		return !$this->getEffect()->IsFresh();
	}

	protected function getEffect(): PotionEffect {
		if (!$this->effect) {
			$this->effect = new PotionEffect(State::getInstance());
			$this->effect->setUnit($this->apply->Unit());
			$existing = Lemuria::Score()->find($this->effect);
			if ($existing instanceof PotionEffect) {
				$this->effect = $existing;
			} else {
				Lemuria::Score()->add($this->effect);
			}
		}
		return $this->effect;
	}
}
