<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * Delete all talent effects that have been persisted erroneously.
 */
final class ClearTalentEffects extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$score = Lemuria::Score();
		foreach ($score as $effect) {
			if ($effect instanceof TalentEffect) {
				$score->remove($effect);
				Lemuria::Log()->debug('Talent effect of ' . $effect->Unit() . ' has been removed.');
			}
		}
	}
}
