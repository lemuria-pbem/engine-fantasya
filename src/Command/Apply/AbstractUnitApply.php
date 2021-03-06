<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Effect\PotionEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

abstract class AbstractUnitApply extends AbstractApply
{
	use MessageTrait;

	protected ?PotionEffect $effect = null;

	protected ?string $applyMessage = null;

	private bool $isNew = false;

	public function CanApply(): bool {
		$effect = $this->getEffect();
		return $this->isNew || !$effect->IsFresh();
	}

	public function apply(): int {
		$count = $this->apply->Count();
		$this->getEffect()->setCount($count);
		$this->applyMessage();
		return $count;
	}

	protected function getEffect(): PotionEffect {
		if (!$this->effect) {
			$this->effect = new PotionEffect(State::getInstance());
			$this->effect->setUnit($this->apply->Unit());
			$existing = Lemuria::Score()->find($this->effect);
			if ($existing instanceof PotionEffect) {
				$this->effect = $existing;
			} else {
				$this->isNew = true;
				$potion      = $this->apply->Potion();
				$this->effect->setPotion($potion)->setWeeks($potion->Weeks());
				Lemuria::Score()->add($this->effect);
			}
		}
		return $this->effect;
	}

	protected function applyMessage(): void {
		if ($this->applyMessage) {
			$this->message($this->applyMessage, $this->apply->Unit());
		}
	}
}
