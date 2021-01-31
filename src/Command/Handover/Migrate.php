<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Handover;

use Lemuria\Engine\Lemuria\Command\GiftTrait;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Party\MigrateFailedMessage;
use Lemuria\Engine\Lemuria\Message\Party\MigrateFromMessage;
use Lemuria\Engine\Lemuria\Message\Party\MigrateIncompatibleMessage;
use Lemuria\Engine\Lemuria\Message\Party\MigrateRejectedMessage;
use Lemuria\Engine\Lemuria\Message\Party\MigrateToMessage;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Race\Human;

/**
 * Implementation of command GIB.
 *
 * The command transfers commodities to another Unit.
 *
 * - GIB <Unit> Einheit
 */
final class Migrate extends UnitCommand
{
	use BuilderTrait;
	use GiftTrait;

	protected function run(): void {
		$i               = 1;
		$this->recipient = $this->nextId($i);

		if (!$this->recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}
		if (strtolower($this->phrase->getParameter(2)) !== 'einheit') {
			throw new UnknownCommandException($this);
		}

		$from = $this->unit->Party();
		$to   = $this->recipient->Party();
		if ($to->Race() === $this->unit->Race() || $to->Race() === self::createRace(Human::class)) {
			if ($this->checkPermission()) {
				$from->People()->remove($this->unit);
				$to->People()->add($this->unit);
				$this->message(MigrateFromMessage::class, $from)->e($this->unit)->e($to, MigrateFromMessage::PARTY);
				$this->message(MigrateToMessage::class, $to)->e($this->unit);
			} else {
				$this->message(MigrateFailedMessage::class, $from)->e($this->unit)->e($to, MigrateFromMessage::PARTY);
				$this->message(MigrateRejectedMessage::class, $to)->e($this->unit);
			}
		} else {
			$this->message(MigrateIncompatibleMessage::class, $from)->e($this->unit)->e($to, MigrateIncompatibleMessage::PARTY);
		}
	}
}
