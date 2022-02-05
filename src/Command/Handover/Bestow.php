<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\ContactTrait;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\OperateTrait;
use Lemuria\Engine\Fantasya\Message\Unit\BestowRejectedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveNotFoundMessage;

/**
 * Implementation of command GEBEN for unica.
 *
 * The command transfers unica to another Unit.
 *
 * - GEBEN <Unit> <Unicum>
 * - GEBEN <Unit> <composition> <Unicum>
 */
final class Bestow extends UnitCommand implements Operator
{
	use ContactTrait;
	use GiftTrait;
	use OperateTrait;

	protected function run(): void {
		$i         = 1;
		$recipient = $this->nextId($i);

		if (!$recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}
		if ($recipient->Region() !== $this->unit->Region()) {
			$this->message(GiveNotFoundMessage::class)->e($recipient);
			return;
		}
		$isVisible = $this->checkVisibility($this->unit, $recipient);
		if (!$this->checkPermission()) {
			if ($isVisible) {
				$this->message(GiveFailedMessage::class)->e($recipient);
				$this->message(BestowRejectedMessage::class, $recipient)->e($this->unit);
				return;
			}
			$this->message(GiveNotFoundMessage::class)->e($recipient);
		}
		if (!$isVisible) {
			$this->message(GiveNotFoundMessage::class)->e($recipient);
			return;
		}

		$this->parseBestow()?->give($recipient);
	}
}
