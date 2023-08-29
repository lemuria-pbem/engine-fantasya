<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * This event prepares the NPCs' turn.
 */
final class NPC extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$count = 0;
		foreach (Party::all() as $party) {
			if ($party->Type() === Type::NPC) {
				foreach ($party->People()->getClone() as $unit) {
					if ($unit->Size() > 0) {
						$count++;
					}
				}
			}
		}
		Lemuria::Log()->debug('Turn for ' . $count . ' NPC units has been added.');
	}
}
