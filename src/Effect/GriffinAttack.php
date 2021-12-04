<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class GriffinAttack extends AbstractRegionEffect
{
	private int $peasants = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	public function Peasants(): int {
		return $this->peasants;
	}

	public function setPeasants(int $peasants): GriffinAttack {
		$this->peasants = $peasants;
		return $this;
	}

	protected function run(): void {
		//TODO delete griffin units
		Lemuria::Score()->remove($this);
	}
}
