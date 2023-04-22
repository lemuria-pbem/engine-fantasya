<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\ContactTrait;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Party\MigrateFailedMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateFromMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateIncompatibleMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateRejectedMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateSameMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateToMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MigrateInvisibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MigrateNotFoundMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Reassignment;

/**
 * Implementation of command GIB.
 *
 * The command transfers commodities to another Unit.
 *
 * - GIB <Unit> Einheit
 */
final class Migrate extends UnitCommand implements Reassignment
{
	use BuilderTrait;
	use ContactTrait;
	use GiftTrait;
	use ReassignTrait;

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
			$this->message(MigrateInvisibleMessage::class)->e($this->recipient);
			return;
		}

		$from = $this->unit->Party();
		$to   = $this->recipient->Party();
		if ($to === $from) {
			$this->message(MigrateSameMessage::class, $from)->e($this->unit);
		} elseif ($to->Race() === $this->unit->Race() || $to->Race() === self::createRace(Human::class)) {
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

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter(1, $old, $new);
	}
}
