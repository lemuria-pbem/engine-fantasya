<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Unit\LayaboutMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Unit;

/**
 * Add layabout message for units that had no activity.
 */
final class Layabout extends AbstractEvent
{
	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		$count = 0;
		foreach (Lemuria::Catalog()->getAll(Catalog::UNITS) as $unit /* @var Unit $unit */) {
			if (!$this->state->getProtocol($unit)->hasActivity()) {
				$this->message(LayaboutMessage::class, $unit);
				$count++;
			}
		}
		Lemuria::Log()->debug($count . ' units had no activity.');
	}
}
