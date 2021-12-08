<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Message\Party\MigrateFailedMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateFromMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateIncompatibleMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateRejectedMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateToMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MigrateNotFoundMessage;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Race\Human;

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
	use CamouflageTrait;
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
		if ($this->recipient->Region() !== $this->unit->Region()) {
			$this->message(MigrateNotFoundMessage::class)->e($this->recipient);
			return;
		}
		if (!$this->checkVisibility($this->unit, $this->recipient)) {
			$this->message(MigrateNotFoundMessage::class)->e($this->recipient);
			return;
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
			$this->message(MigrateIncompatibleMessage::class, $from)->e($this->unit)->e($to, MigrateFromMessage::PARTY);
		}
	}
}
