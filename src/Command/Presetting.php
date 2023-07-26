<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Party\PresettingBattleRowMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingDisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingDisguisePartyMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingDisguiseDoesNotKnowMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingExploreDepartMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingExploreLandMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingExploreMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingExploreNotMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingHideMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingLootMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingNoDisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingNoHideMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingNoLootMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingDisguiseUnknownMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingNoRepeatMessage;
use Lemuria\Engine\Fantasya\Message\Party\PresettingRepeatMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Exploring;

/**
 * This command is used to set default behaviour of newly recruited units.
 *
 * - VORGABE Erkunden|Erkundung [Ablegen|Anlegen|Nicht]
 * - VORGABE Kampf|Kaempfe|Kaempfen|Kämpfe|Kämpfen <battle row>
 * - VORGABE Sammel|Sammeln|Sammle [Nicht]
 * - VORGABE Tarne|Tarnen|Tarnung [Nicht]
 * - VORGABE Tarne|Tarnen|Tarnung Partei [<Party>]
 * - VORGABE Tarne|Tarnen|Tarnung Partei Nicht
 * - VORGABE Wiederhole|Wiederholen|Wiederholung [Nicht]
 */
final class Presetting extends UnitCommand
{
	private Party $party;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->party = $this->unit->Party();
	}

	protected function run(): void {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}

		$what = mb_strtolower($this->phrase->getParameter());
		$how  = strtolower($this->phrase->getParameter(2));
		switch ($what) {
			case 'erkunden' :
			case 'erkundung' :
				$this->setExploring($how);
				break;
			case 'kampf' :
			case 'kaempfe' :
			case 'kaempfen' :
			case 'kämpfe' :
			case 'kämpfen' :
				$this->setBattleRow($how);
				break;
			case 'sammel' :
			case 'sammeln' :
			case 'sammle' :
				$this->setIsLooting($how);
				break;
			case 'tarne' :
			case 'tarnen' :
			case 'tarnung' :
				if ($how === 'partei') {
					$this->setDisguise(strtolower($this->phrase->getParameter(3)));
				} else {
					$this->setIsHiding($how);
				}
				break;
			case 'wiederhole' :
			case 'wiederholen' :
			case 'wiederholung':
				$this->setIsRepeat($how);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid parameter "' . $what . '".');
		}
	}

	protected function checkSize(): bool {
		return true;
	}

	protected function setBattleRow(string $position): void {
		try {
			$battleRow = $this->context->Factory()->battleRow($position);
		} catch (InvalidCommandException) {
			throw new InvalidCommandException($this, 'Invalid battle row parameter.');
		}

		$this->party->Presettings()->setBattleRow($battleRow);
		$this->message(PresettingBattleRowMessage::class, $this->party)->p($battleRow->value);
	}

	protected function setIsLooting(string $not): void {
		$isLooting = $this->isNot($not);
		$this->party->Presettings()->setIsLooting($isLooting);
		if ($isLooting) {
			$this->message(PresettingLootMessage::class, $this->party);
		} else {
			$this->message(PresettingNoLootMessage::class, $this->party);
		}
	}

	protected function setIsHiding(string $not): void {
		$isHiding = $this->isNot($not);
		$this->party->Presettings()->setIsHiding($isHiding);
		if ($isHiding) {
			$this->message(PresettingHideMessage::class, $this->party);
		} else {
			$this->message(PresettingNoHideMessage::class, $this->party);
		}
	}

	protected function setDisguise(string $party): void {
		if (empty($party)) {
			$this->party->Presettings()->setDisguise();
			$this->message(PresettingDisguiseMessage::class, $this->party);
			return;
		}
		if ($party === 'nicht') {
			$this->party->Presettings()->setDisguise(false);
			$this->message(PresettingNoDisguiseMessage::class, $this->party);
			return;
		}

		$partyId = $this->toId($party);
		try {
			$party = Party::get($partyId);
			if (!$this->party->Diplomacy()->isKnown($party)) {
				$this->message(PresettingDisguiseDoesNotKnowMessage::class, $this->party)->e($party);
				$party = null;
			}
		} catch (NotRegisteredException) {
			$party = null;
		}

		if ($party) {
			if ($party === $this->party) {
				$this->party->Presettings()->setDisguise();
				$this->message(PresettingDisguiseMessage::class, $this->party);
			} else {
				$this->party->Presettings()->setDisguise($partyId);
				$this->message(PresettingDisguisePartyMessage::class, $this->party)->e($party);
			}
		} else {
			$this->message(PresettingDisguiseUnknownMessage::class, $this->party)->p((string)$partyId);
		}
	}

	protected function setIsRepeat(string $not): void {
		$isRepeat = $this->isNot($not);
		$this->party->Presettings()->setIsRepeat($isRepeat);
		if ($isRepeat) {
			$this->message(PresettingRepeatMessage::class, $this->party);
		} else {
			$this->message(PresettingNoRepeatMessage::class, $this->party);
		}
	}

	protected function setExploring(string $how): void {
		switch ($how) {
			case '' :
				$this->party->Presettings()->setExploring(Exploring::Explore);
				$this->message(PresettingExploreMessage::class, $this->party);
				break;
			case 'nicht' :
				$this->party->Presettings()->setExploring(Exploring::Not);
				$this->message(PresettingExploreNotMessage::class, $this->party);
				break;
			case 'anlegen' :
				$this->party->Presettings()->setExploring(Exploring::Land);
				$this->message(PresettingExploreLandMessage::class, $this->party);
				break;
			case 'ablegen' :
				$this->party->Presettings()->setExploring(Exploring::Depart);
				$this->message(PresettingExploreDepartMessage::class, $this->party);
				break;
			default :
				throw new InvalidCommandException($this);
		}
	}

	private function isNot(string $not): bool {
		try {
			return match ($not) {
				''      => true,
				'nicht' => false
			};
		} catch (\UnhandledMatchError $e) {
			throw new InvalidCommandException($this, previous: $e);
		}
	}
}
