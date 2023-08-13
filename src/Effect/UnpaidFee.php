<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class UnpaidFee extends AbstractUnitEffect
{
	protected ?bool $isReassign = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
