<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Engine\Fantasya\Effect\SpyEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census as ModelCensus;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

class Census extends ModelCensus
{
	/**
	 * Get the affiliated party of a unit.
	 */
	public function getParty(Unit $unit): ?Party {
		$party   = $this->Party();
		$foreign = $unit->Party();
		// Own unit.
		if ($foreign === $party) {
			return $party;
		}

		$disguise = $unit->Disguise();
		// Undisguised unit.
		if ($disguise === false) {
			return $foreign;
		}
		// DISGUISE relation.
		if ($unit->Party()->Diplomacy()->has(Relation::DISGUISE, $party)) {
			return $unit->Party();
		}

		$effect = new ContactEffect(State::getInstance());
		$effect = Lemuria::Score()->find($effect->setParty($party));
		// Contact order given.
		if ($effect instanceof ContactEffect && $effect->From()->has($unit->Id())) {
			return $foreign;
		}
		$effect = new SpyEffect(State::getInstance());
		$effect = Lemuria::Score()->find($effect->setParty($party));
		// Unit has been spied with disguise revealed.
		if ($effect instanceof SpyEffect && $effect->isRevealed($unit)) {
			return $foreign;
		}
		// Disguised unit.
		return $disguise;
	}
}
