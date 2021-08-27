<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

trait UnitTrait
{
	use ContextTrait;

	protected Unit $unit;

	protected function commitCommand(UnitCommand $command): void {
		$protocol = $this->context->getProtocol($this->unit);
		if (!$protocol->commit($command)) {
			throw new ActivityException($command);
		}
	}

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
		if (!$this->unit->Construction() && $this->unit->IsHiding() && !$this->unit->IsGuarding()) {
			$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
		}

		foreach ($intelligence->getGuards() as $guard /* @var Unit $guard */) {
			$guardParty = $guard->Party();
			if ($guardParty !== $party) {
				if ($this->context->getTurnOptions()->IsSimulation()) {
					$guardParties[$guardParty->Id()->Id()] = $guardParty;
				} elseif (!$guardParty->Diplomacy()->has($agreement, $this->unit)) {
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
