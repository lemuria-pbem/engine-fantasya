<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class Daydream extends AbstractUnitEffect
{
	private int $level = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	public function Level(): int {
		return $this->level;
	}

	public function setLevel(int $level): Daydream {
		$this->level = $level;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
