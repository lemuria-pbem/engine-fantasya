<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\LemuriaScore;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * Executes effects that need aftercare.
 */
final class Aftercare extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	/** @noinspection PhpConditionAlreadyCheckedInspection */
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
