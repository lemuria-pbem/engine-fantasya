<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\LemuriaScore;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * Executes effects that need aftercare.
 */
final class Aftercare extends AbstractEvent
{
	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		$score = Lemuria::Score();
		if ($score instanceof LemuriaScore) {
			foreach ($score->getAftercareEffects() as $effect) {
				$effect->prepare();
				if ($effect->isPrepared()) {
					$effect->execute();
				}
			}
		}
	}
}
