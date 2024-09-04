<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Party\FiefPartyMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateFromMessage;
use Lemuria\Engine\Fantasya\Message\Party\MigrateToMessage;
use Lemuria\Engine\Fantasya\Message\Region\FiefRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefCentralMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefGovernmentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefNoHeirMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefNotInCastleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FiefSelfMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantTakeoverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TransportNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardMessage;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quota;
use Lemuria\Model\Fantasya\Quotas;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Herballore;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of command GEBEN Reich.
 *
 * The command transfers a whole realm to another party.
 *
 * - GEBEN <Unit> Reich
 * - GEBEN <Unit> Reich Vollständig
 */
final class Fief extends UnitCommand
{
	private Realm $realm;

	private Construction $castle;

	private Unit $heir;

	private Party $party;

	protected function run(): void {
		if (!$this->checkRealm()) {
			return;
		}

		$n = $this->phrase->count();
		if ($n === 3) {
			$complete = mb_strtolower($this->phrase->getParameter(3));
			if (!in_array($complete, ['vollständig', 'vollstaendig'])) {
				throw new InvalidCommandException($this);
			}
		}

		if (!$this->parseHeir()) {
			return;
		}
		$n === 3 ? $this->handOverCompletely() : $this->handOverMinimum();
	}

	private function checkRealm(): bool {
		$region = $this->unit->Region();
		$realm  = $region->Realm();
		if (!$realm) {
			$this->message(FiefNoneMessage::class);
			return false;
		}

		$this->realm = $realm;
		if ($realm->Territory()->Central() !== $region) {
			$this->message(FiefCentralMessage::class);
			return false;
		}

		$castle = $this->context->getIntelligence($region)->getGovernment();
		if ($castle?->Inhabitants()->Owner() !== $this->unit) {
			$this->message(FiefGovernmentMessage::class);
			return false;
		}

		$this->castle = $castle;
		return true;
	}

	private function parseHeir(): bool {
		$i    = 1;
		$id   = null;
		$unit = $this->nextId($i, $id);
		if (!$unit || !$this->calculus()->canDiscover($unit)) {
			$this->message(FiefNotFoundMessage::class)->p((string)$id);
			return false;
		}
		$this->heir = $unit;

		$this->party = $unit->Party();
		if ($this->party === $this->unit->Party()) {
			$this->message(FiefSelfMessage::class);
			return false;
		}
		$isSimulation = $this->context->getTurnOptions()->IsSimulation();
		if ($isSimulation || !$this->party->Diplomacy()->has(Relation::GIVE, $this->unit)) {
			$this->message(GiveFailedMessage::class)->e($unit);
			return false;
		}
		return true;
	}

	private function handOverMinimum(): void {
		if ($this->unit->Construction() !== $this->castle) {
			$this->message(FiefNotInCastleMessage::class)->e($this->heir);
			return;
		}
		$heirs = $this->getHeirs();
		if (!$heirs) {
			return;
		}

		$former = $this->realm->Party();
		$former->Possessions()->remove($this->realm);
		$this->party->Possessions()->add($this->realm);
		$region = $this->castle->Region();
		$this->message(FiefRegionMessage::class, $region)->p($this->realm->Name())->p($this->party->Name(), FiefRegionMessage::HEIR);
		$this->message(FiefPartyMessage::class, $this->party)->p($this->realm->Name());
		$this->castle->Inhabitants()->setOwner($this->heir);
		$this->message(GrantMessage::class)->e($this->heir);
		$this->message(GrantTakeoverMessage::class, $this->heir)->e($this->unit);
		foreach ($heirs as $castle) {
			$inhabitants = $castle->Inhabitants();
			$owner = $inhabitants->Owner();
			foreach ($inhabitants as $heir) {
				if ($heir->Party() === $this->party) {
					$inhabitants->setOwner($heir);
					$this->message(GrantMessage::class, $owner)->e($heir);
					$this->message(GrantTakeoverMessage::class, $heir)->e($owner);
					break;
				}
			}
		}

		foreach ($this->realm->Territory() as $region) {
			foreach ($this->context->getIntelligence($region)->getGuards() as $guard) {
				if ($guard->Party() === $former) {
					$guard->setIsGuarding(false);
					$this->message(UnguardMessage::class, $guard);
				}
			}
			foreach ($region->Residents() as $unit) {
				if ($unit->IsTransporting() && $unit->Party() === $former) {
					$unit->setIsTransporting(false);
					$this->message(TransportNotMessage::class, $unit);
				}
			}
			$former->Regulation()->getQuotas($region)?->clear();
			$former->Regulation()->offsetUnset($region->Id());
		}
	}

	private function handOverCompletely(): void {
		$castles = $this->getCastles();
		$former  = $this->realm->Party();
		$former->Possessions()->remove($this->realm);
		$this->party->Possessions()->add($this->realm);
		$this->message(FiefPartyMessage::class, $this->party)->p($this->realm->Name());

		$from          = $former->People();
		$to            = $this->party->People();
		$race          = $this->party->Race();
		$isHuman       = $race instanceof Human;
		$regulation    = $this->party->Regulation();
		$magic         = self::createTalent(Magic::class);
		$hasMages      = false;
		$herballore    = self::createTalent(Herballore::class);
		$hasHerbalists = false;
		foreach ($this->realm->Territory() as $region) {
			foreach ($region->Residents() as $unit) {
				if ($unit->Party() === $former) {
					if ($isHuman || $unit->Race() === $race) {
						$from->remove($unit);
						$to->add($unit);
						$knowledge = $unit->Knowledge();
						if ($knowledge->offsetExists($magic)) {
							$hasMages = true;
						}
						if ($knowledge->offsetExists($herballore)) {
							$hasHerbalists = true;
						}
						$this->message(MigrateFromMessage::class, $former)->e($unit)->e($this->party, MigrateFromMessage::PARTY);
						$this->message(MigrateToMessage::class, $this->party)->e($unit);
					} else {
						if ($unit->IsGuarding()) {
							$unit->setIsGuarding(false);
							$this->message(UnguardMessage::class, $unit);
						}
						if ($unit->IsTransporting()) {
							$unit->setIsTransporting(false);
							$this->message(TransportNotMessage::class, $unit);
						}
					}
				}
			}
			$formerRegulation = $former->Regulation();
			$oldQuotas        = $formerRegulation->getQuotas($region);
			if ($oldQuotas && !$oldQuotas->isEmpty()) {
				$quotas = $regulation->add($region)->getQuotas($region);
				foreach ($oldQuotas as $quota) {
					$this->setQuota($quotas, $quota);
				}
			}
			$oldQuotas->clear();
			$formerRegulation->offsetUnset($region->Id());
		}

		if ($hasMages) {
			$this->handOverSpellsFrom($former);
		}
		if ($hasHerbalists) {
			$this->handOverHerbagesFrom($former);
		}

		foreach ($castles as $castle) {
			$inhabitants = $castle->Inhabitants();
			if ($inhabitants->Owner()->Party() !== $this->party) {
				foreach ($inhabitants as $heir) {
					$owner = $inhabitants->Owner();
					if ($heir->Party() === $this->party) {
						$inhabitants->setOwner($heir);
						$this->message(GrantMessage::class, $owner)->e($heir);
						$this->message(GrantTakeoverMessage::class, $heir)->e($owner);
						break;
					}
				}
			}
		}
	}

	/**
	 * @return Construction[]|null
	 */
	private function getHeirs(): ?array {
		$territory = $this->realm->Territory();
		$central   = $territory->Central();
		$heirs     = [];
		foreach ($territory as $region) {
			if ($region !== $central) {
				$castle = $this->context->getIntelligence($region)->getGovernment();
				if ($castle) {
					foreach ($castle->Inhabitants() as $unit) {
						if ($unit->Party() === $this->party) {
							$heirs[$region->Id()->Id()] = $castle;
							continue 2;
						}
					}
				} else {
					foreach ($region->Residents() as $unit) {
						if ($unit->Party() === $this->party) {
							continue 2;
						}
					}
				}
				$this->message(FiefNoHeirMessage::class)->e($region)->p($this->party->Name());
				return null;
			}
		}
		return $heirs;
	}

	/**
	 * @return Construction[]
	 */
	private function getCastles(): array {
		$castles = [];
		foreach ($this->realm->Territory() as $region) {
			$castles[] = $this->context->getIntelligence($region)->getGovernment();
		}
		return $castles;
	}

	private function setQuota(Quotas $quotas, Quota $quota): void {
		$commodity = $quota->Commodity();
		$existing  = $quotas->getQuota($commodity);
		if ($existing) {
			$existing->setThreshold($quota->Threshold());
		} else {
			$quotas->add(new Quota($commodity, $quota->Threshold()));
		}
	}

	private function handOverSpellsFrom(Party $former): void {
		$spellBook = $this->party->SpellBook();
		foreach ($former->SpellBook() as $spell) {
			if (!$spellBook->offsetExists($spell)) {
				$spellBook->add($spell);
			}
		}
	}

	private function handOverHerbagesFrom(Party $former): void {
		$herbalBook = $this->party->HerbalBook();
		$source     = $former->HerbalBook();
		foreach ($source as $region) {
			if ($herbalBook->has($region->Id())) {
				$round = $source->getVisit($region)->Round();
				if ($round > $herbalBook->getVisit($region)->Round()) {
					$herbalBook->record($region, $source->getHerbage($region), $round);
				}
			} else {
				$herbalBook->record($region, $source->getHerbage($region), $source->getVisit($region)->Round());
			}
		}
	}
}
