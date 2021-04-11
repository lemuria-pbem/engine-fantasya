<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

trait UnitTrait
{
	use ContextTrait;

	protected Unit $unit;

	/**
	 * Check region guards before allocation.
	 *
	 * If region is guarded by other parties and there are no specific agreements, this unit may only produce if it is
	 * not in a building and has better camouflage than all the blocking guards' perception.
	 *
	 * @return Party[]
	 */
	protected function getCheckByAgreement(int $agreement): array {
		$guardParties = [];
		$party        = $this->unit->Party();
		$context      = $this->context;
		$intelligence = $context->getIntelligence($this->unit->Region());
		$camouflage   = PHP_INT_MIN;
		if (!$this->unit->Construction()) {
			$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
		}

		foreach ($intelligence->getGuards() as $guard /* @var Unit $guard */) {
			$guardParty = $guard->Party();
			if ($guardParty !== $party) {
				if (!$guardParty->Diplomacy()->has($agreement, $this->unit)) {
					$perception = $context->getCalculus($guard)->knowledge(Perception::class)->Level();
					if ($perception >= $camouflage) {
						$guardParties[$guardParty->Id()->Id()] = $guardParty;
					}
				}
			}
		}

		return $guardParties;
	}
}
