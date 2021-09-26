<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Potion;

class Fighter
{
	public ?Potion $potion = null;

	private bool $hasBeenHealed = false;

	public function __construct(public int $health) {
	}

	public function HasBeenHealed(): bool {
		return $this->hasBeenHealed;
	}

	public function heal(): Fighter {
		$this->health = 1;
		$this->potion = null;
		$this->hasBeenHealed = true;
		return $this;
	}
}
