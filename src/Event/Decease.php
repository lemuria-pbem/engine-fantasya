<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Message\Party\DeceaseMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Unit;

/**
 * Units that have no health left will die.
 */
final class Decease extends AbstractEvent
{
	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::PARTIES) as $party /* @var Party $party */) {
			Lemuria::Log()->debug('Running Decease for Party ' . $party->Id() . '.', ['party' => $party]);
			$units = $party->People();
			foreach ($units as $unit /* @var Unit $unit */) {
				if ($unit->Health() <= 0.0) {
					$unit->setSize(0);
					$this->message(DeceaseMessage::class, $party)->e($unit);
				}
			}
		}
	}
}
