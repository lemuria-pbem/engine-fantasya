<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\State;

final class Hunger extends AbstractEffect
{
	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		//TODO reduce hitpoints
	}
}
