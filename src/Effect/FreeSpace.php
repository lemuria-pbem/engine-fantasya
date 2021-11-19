<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class FreeSpace extends AbstractConstructionEffect
{
	private int $space = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
	}

	public function Space(): int {
		return $this->space;
	}

	public function addSpace(int $space): FreeSpace {
		$this->space += $space;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
