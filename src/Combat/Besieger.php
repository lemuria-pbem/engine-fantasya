<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\People;

class Besieger
{
	protected People $besiegers;

	protected bool $sieged = false;

	public function __construct(protected Construction $construction) {
		$this->besiegers = new People();
	}

	public function IsSieged(): bool {
		return $this->sieged;
	}

	public function Besiegers(): People {
		return $this->besiegers;
	}

	public function siege(): static {
		$this->sieged = true;
		return $this;
	}
}
