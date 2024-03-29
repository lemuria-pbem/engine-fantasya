<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;

final class InvisibleEnemy extends AbstractUnitEffect
{
	protected ?bool $isReassign = null;

	private People $from;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->from = new People();
	}

	public function From(): People {
		return $this->from;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
