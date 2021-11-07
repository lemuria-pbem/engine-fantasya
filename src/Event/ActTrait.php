<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Unit;

trait ActTrait
{
	protected Unit $unit;

	public function __construct(Behaviour $behaviour) {
		$this->unit = $behaviour->Unit();
	}

	#[Pure] protected function getMonster(): ?Monster {
		$race = $this->unit->Race();
		return $race instanceof Monster ? $race : null;
	}
}
