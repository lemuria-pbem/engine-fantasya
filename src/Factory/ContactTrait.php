<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

trait ContactTrait
{
	use ContextTrait;

	/**
	 * Check target unit's visibility for contacting or gifts.
	 */
	private function checkVisibility(Unit $unit, Unit $target): bool {
		$party = $unit->Party();
		$other = $target->Party();
		if ($other === $party) {
			return true;
		}
		$outlook     = new Outlook(new Census($party));
		$apparitions = $outlook->getContacts($target->Region());
		if ($apparitions->has($target->Id())) {
			return true;
		}
		return !$this->context->getTurnOptions()->IsSimulation() && $other->Diplomacy()->has(Relation::PERCEPTION, $unit);
	}
}
