<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Party\DeceaseMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

/**
 * Units that have no health left will die.
 */
final class Decease extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
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
