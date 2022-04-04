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
		$i               = 1;
		$this->recipient = $this->nextId($i);

		if (!$this->recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}
		if ($this->recipient->Region() !== $this->unit->Region()) {
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
			return;
		}
		$isVisible = $this->checkVisibility($this->unit, $this->recipient);
		if (!$this->checkPermission()) {
			if ($isVisible) {
				$this->message(GiveFailedMessage::class)->e($this->recipient);
				$this->message(BestowRejectedMessage::class, $this->recipient)->e($this->unit);
				return;
			}
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
		}
		if (!$isVisible) {
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
			return;
		}

		$this->parseBestow()?->give($this->recipient);
	}
}
