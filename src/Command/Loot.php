<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Party\LootAddGroupMessage;
use Lemuria\Engine\Fantasya\Message\Party\LootAllMessage;
use Lemuria\Engine\Fantasya\Message\Party\LootCommodityMessage;
use Lemuria\Engine\Fantasya\Message\Party\LootCommodityNotMessage;
use Lemuria\Engine\Fantasya\Message\Party\LootNothingMessage;
use Lemuria\Engine\Fantasya\Message\Party\LootRemoveGroupMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Loot as LootModel;

/**
 * This command is used to set the party's loot.
 *
 * - BEUTE Alles|Nichts
 * - BEUTE <Group> [Nicht]
 * - BEUTE <Commodity> [Nicht]
 */
final class Loot extends UnitCommand
{
	protected function run(): void {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}
		$what = mb_strtolower($this->phrase->getParameter());
		$not  = false;
		if ($n === 2) {
			if (strtolower($this->phrase->getParameter(2)) === 'nicht') {
				$not = true;
			} else {
				throw new InvalidCommandException($this);
			}
		} elseif ($n > 2) {
			throw new InvalidCommandException($this);
		}

		$loot = match ($what) {
			'nichts'                                                                        => LootModel::NOTHING,
			'alles'                                                                         => LootModel::ALL,
			'ressource', 'ressourcen', 'rohmaterial', 'rohstoff', 'rohstoffe'               => LootModel::RAW_MATERIAL,
			'luxus', 'luxusgut', 'luxusgueter', 'luxusgüter', 'luxusware', 'luxuswaren'     => LootModel::LUXURY,
			'reittier', 'reittiere', 'tier', 'tiere', 'transport', 'transporter'            => LootModel::TRANSPORT,
			'waffe', 'waffen'                                                               => LootModel::WEAPON,
			'ruestung', 'ruestungen', 'rüstung', 'rüstungen', 'schild', 'schilde', 'schutz' => LootModel::PROTECTION,
			'kraut', 'kraeuter', 'kräuter'                                                  => LootModel::HERB,
			'trank', 'traenke', 'tränke'                                                    => LootModel::POTION,
			'trophaee', 'trophaeen', 'trophäe', 'trophäen'                                  => LootModel::TROPHY,
			default  => $this->context->Factory()->commodity($what)
		};

		$party     = $this->unit->Party();
		$partyLoot = $party->Loot();
		if ($loot instanceof Commodity) {
			if ($not) {
				if ($partyLoot->isWhitelist()) {
					$partyLoot->Classes()->add($loot);
				} else {
					$partyLoot->Classes()->delete($loot);
				}
				$this->message(LootCommodityNotMessage::class, $party)->s($loot);
			} else {
				if ($partyLoot->isWhitelist()) {
					$partyLoot->Classes()->delete($loot);
				} else {
					$partyLoot->Classes()->add($loot);
				}
				$this->message(LootCommodityMessage::class, $party)->s($loot);
			}
		} else {
			if ($not) {
				$partyLoot->remove($loot);
				$this->message(LootRemoveGroupMessage::class, $party)->p($loot);
			} else {
				$partyLoot->set($loot);
				if ($loot === LootModel::NOTHING) {
					$this->message(LootNothingMessage::class, $party);
				} elseif ($loot === LootModel::ALL) {
					$this->message(LootAllMessage::class, $party);
				} else {
					$this->message(LootAddGroupMessage::class, $party)->p($loot);
				}
			}
		}
	}

	#[Pure] protected function checkSize(): bool {
		return true;
	}
}
