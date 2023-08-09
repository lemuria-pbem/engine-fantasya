<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Engine\Fantasya\Exception\AlternativeException;
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
			if ($command instanceof Activity && $command->isAlternative()) {
				throw new AlternativeException($command);
			}
			throw new ActivityException($command);
		}
	}

	/**
	 * Check region guards before allocation.
	 *
	 * If region is guarded by other parties and there are no specific agreements, this unit may only produce if it is
	 * not in a building and has better camouflage than all the blocking guards' perception.
	 *
	 * @return array<Party>
	 */
	protected function getCheckByAgreement(int $agreement): array {
		return $this->getCheckByAgreementForUnit($this->unit, $agreement);
	}

	protected function getCheckByAgreementForUnit(Unit $unit, int $agreement): array {
		$guardParties = [];
		$party        = $unit->Party();
		$context      = $this->context;
		$intelligence = $context->getIntelligence($unit->Region());
		$camouflage   = PHP_INT_MIN;
		if (!$unit->Construction() && $unit->IsHiding() && !$unit->IsGuarding()) {
			$camouflage = $context->getCalculus($unit)->knowledge(Camouflage::class)->Level();
		}

		foreach ($intelligence->getGuards() as $guard) {
			$guardParty = $guard->Party();
			if ($guardParty !== $party) {
				if ($this->context->getTurnOptions()->IsSimulation()) {
					$guardParties[$guardParty->Id()->Id()] = $guardParty;
				} elseif (!$guardParty->Diplomacy()->has($agreement, $unit)) {
					$perception = $context->getCalculus($guard)->knowledge(Perception::class)->Level();
					if ($perception >= $camouflage) {
						$guardParties[$guardParty->Id()->Id()] = $guardParty;
					}
				}
			}
		}

		return $guardParties;
	}

	/**
	 * This method makes conditional debugging easier.
	 */
	protected function halt(mixed $id): bool {
		return is_int($id) ? $this->unit->Id()->Id() === $id : (string)$this->unit->Id() === (string)$id;
	}
}
