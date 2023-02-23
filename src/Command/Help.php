<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\HelpMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpPartyNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpPartyRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpPartyRegionNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpRegionNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpSelfMessage;
use Lemuria\Engine\Fantasya\Message\Unit\HelpUnknownPartyMessage;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Exception\UnknownPartyException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Relation;

/**
 * This command is used to set diplomatic relations.
 *
 * - HELFEN 0|<Party> Abbau|Abbauen|Mache|Machen|Ressourcen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Alles [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Betrete|Betreten [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Bewache|Bewachen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Durchreise|Durchreisen|Reise|Reisen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Gib|Geben [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Handel|Handeln [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Kampf|Kämpfe|Kämpfen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Kontakt|Kontaktiere|Kontaktieren [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Markt [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Nahrung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Nicht|Nichts [Region]
 * - HELFEN 0|<Party> Parteitarnung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Silber [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Tarne|Tarnen|Tarnung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Versorge|Versorgen|Versorgung [Nicht|Region] [Region|Nicht]
 */
final class Help extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 2) {
			throw new InvalidCommandException($this);
		}

		$party = null;
		$p = $this->phrase->getParameter();
		if ($p === '0') {
			$party = $this->unit->Party();
		} else {
			$partyId = $this->toId($p);
			if ($partyId->Id() === $this->unit->Party()->Id()->Id()) {
				$this->message(HelpSelfMessage::class);
				return;
			}
			try {
				$party = Party::get($partyId);
				if (!$this->unit->Party()->Diplomacy()->isKnown($party)) {
					$party = null;
				}
			} catch (NotRegisteredException) {
			}
			if (!$party) {
				$this->message(HelpUnknownPartyMessage::class)->p($partyId->Id());
				return;
			}
		}

		$p         = $this->phrase->getParameter(2);
		$agreement = $this->getAgreement($p);
		if ($agreement === null) {
			throw new InvalidCommandException($this, 'Invalid agreement.');
		}

		$isNot    = false;
		$inRegion = false;
		if ($n > 2) {
			$p = $this->phrase->getParameter(3);
			$this->parseAdditionalParameter($p, $isNot, $inRegion);
		}
		if ($n > 3) {
			$p = $this->phrase->getParameter(4);
			$this->parseAdditionalParameter($p, $isNot, $inRegion);
		}
		if ($n > 4 || $agreement === Relation::NONE && $isNot) {
			throw new InvalidCommandException($this, 'Invalid negation of agreement NONE.');
		}

		$this->updateDiplomacy($party, $inRegion, $agreement, $isNot);
	}

	protected function checkSize(): bool {
		return true;
	}

	private function getAgreement(string $agreement): ?int {
		return match (mb_strtolower($agreement)) {
			'abbau', 'abbauen', 'mache', 'machen', 'ressourcen' => Relation::RESOURCES,
			'alles'                                             => Relation::ALL,
			'betrete', 'betreten'                               => Relation::ENTER,
			'bewache', 'bewachen'                               => Relation::GUARD,
			'durchreise', 'durchreisen', 'reise', 'reisen'      => Relation::PASS,
			'gib', 'geben'                                      => Relation::GIVE,
			'handel', 'handeln'                                 => Relation::TRADE,
			'kampf', 'kämpfe', 'kämpfen'                        => Relation::COMBAT,
			'kontakt', 'kontaktiere', 'kontaktieren'            => Relation::TELL,
			'markt'                                             => Relation::MARKET,
			'nahrung'                                           => Relation::FOOD,
			'nicht', 'nichts'                                   => Relation::NONE,
			'parteitarnung'                                     => Relation::DISGUISE,
			'silber'                                            => Relation::SILVER,
			'tarne', 'tarnen', 'tarnung'                        => Relation::PERCEPTION,
			'versorge', 'versorgen', 'versorgung'               => Relation::EARN,
			default                                             => null
		};
	}

	/**
	 * @throws InvalidCommandException
	 */
	private function parseAdditionalParameter(string $p, bool &$isNot, bool &$inRegion): void {
		switch (strtolower($p)) {
			case 'nicht' :
				$isNot = true;
				break;
			case 'region' :
				$inRegion = true;
				break;
			default :
				throw new InvalidCommandException($this);
		}
	}

	private function updateDiplomacy(Party $party, bool $inRegion, int $agreement, bool $isNot): void {
		$diplomacy = $this->unit->Party()->Diplomacy();
		$region    = $inRegion ? $this->unit->Region() : null;
		$relation  = new Relation($party, $region);
		if ($diplomacy->offsetExists($relation)) {
			$relation = $diplomacy->offsetGet($relation);
		} else {
			try {
				$diplomacy->add($relation);
			} catch (UnknownPartyException) {
				$this->message(HelpUnknownPartyMessage::class)->e($party);
				return;
			}
		}

		if ($isNot) {
			$relation->remove($agreement);
		} elseif ($agreement === Relation::NONE) {
			$diplomacy->delete($relation);
		} else {
			$relation->add($agreement);
		}

		if ($inRegion) {
			if ($party === $this->unit->Party()) {
				if ($isNot) {
					$this->message(HelpRegionNotMessage::class)->e($region)->p($agreement);
				} else {
					$this->message(HelpRegionMessage::class)->e($region)->p($agreement);
				}
			} else {
				if ($isNot) {
					$this->message(HelpPartyRegionNotMessage::class)->e($party)->e($region, HelpPartyRegionMessage::REGION)->p($agreement);
				} else {
					$this->message(HelpPartyRegionMessage::class)->e($party)->e($region, HelpPartyRegionMessage::REGION)->p($agreement);
				}
			}
		} else {
			if ($party === $this->unit->Party()) {
				if ($isNot) {
					$this->message(HelpNotMessage::class)->p($agreement);
				} else {
					$this->message(HelpMessage::class)->p($agreement);
				}
			} else {
				if ($isNot) {
					$this->message(HelpPartyNotMessage::class)->e($party)->p($agreement);
				} else {
					$this->message(HelpPartyMessage::class)->e($party)->p($agreement);
				}
			}
		}
	}
}
