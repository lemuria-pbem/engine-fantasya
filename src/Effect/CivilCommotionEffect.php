<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class CivilCommotionEffect extends AbstractRegionEffect
{
	protected ?bool $isReassign = null;

	private bool $dissolve = true;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function keep(): CivilCommotionEffect {
		$this->dissolve = false;
		return $this;
	}

	protected function run(): void {
		if ($this->dissolve) {
			Lemuria::Score()->remove($this);
		}
	}
}
