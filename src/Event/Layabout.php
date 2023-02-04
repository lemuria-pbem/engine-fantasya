<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Unit\LayaboutMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * Add layabout message for units that had no activity.
 */
final class Layabout extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		$count = 0;
		foreach (Party::all() as $party) {
			if ($party->Type() === Type::Player && !$party->hasRetired()) {
				foreach ($party->People() as $unit) {
					if (!$this->state->getProtocol($unit)->hasActivity()) {
						$this->message(LayaboutMessage::class, $unit);
						$count++;
					}
				}
			}
		}
		Lemuria::Log()->debug($count . ' units had no activity.');
	}
}
