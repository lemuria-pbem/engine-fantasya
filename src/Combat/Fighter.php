<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Potion;

class Fighter
{
	public ?Potion $potion = null;

	public int $quickening = 0;

	private int $features = 0;

	private bool $hasBeenHealed = false;

	public function __construct(public int $health) {
	}

	public function HasBeenHealed(): bool {
		return $this->hasBeenHealed;
	}

	public function hasFeature(Feature $feature): bool {
		return ($this->features & $feature->value) === $feature->value;
	}

	public function setFeature(Feature $feature, bool $active = true): Fighter {
		if ($active) {
			$this->features |= $feature->value;
		} else {
			$this->features &= Feature::SIZE - $feature->value;
		}
		return $this;
	}

	public function heal(): Fighter {
		$this->health = 1;
		$this->potion = null;
		$this->hasBeenHealed = true;
		return $this;
	}
}
