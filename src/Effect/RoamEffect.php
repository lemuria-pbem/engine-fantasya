<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class RoamEffect extends AbstractUnitEffect
{
	public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
