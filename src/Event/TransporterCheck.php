<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Event\TransporterCheckNoRidingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Event\TransporterCheckTooHeavyMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Travel\Transport;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;

/**
 * Add failure message for unit that cannot transport in realm.
 */
final class TransporterCheck extends AbstractEvent
{
	use BuilderTrait;
	use MessageTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
	}

	protected function run(): void {
		foreach (Unit::all() as $unit) {
			if ($unit->IsTransporting()) {
				$transport = Transport::check($this->context->getCalculus($unit)->getTrip());
				if ($transport === Transport::TOO_HEAVY) {
					$this->message(TransporterCheckTooHeavyMessage::class, $unit);
				} elseif ($transport === Transport::NO_RIDING) {
					$this->message(TransporterCheckNoRidingMessage::class, $unit);
				}
			}
		}
	}
}
