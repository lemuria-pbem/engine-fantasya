<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\AttackAllyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackSelfMessage;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * Attacks units.
 *
 * - ATTACKIEREN <Unit>...
 */
final class Attack extends UnitCommand
{
	use CamouflageTrait;

	/**
	 * @var Unit[]
	 */
	private array $units = [];

	protected function initialize(): void {
		parent::initialize();
		if ($this->unit->BattleRow() <= Combat::BYSTANDER) {
			$this->message(AttackNotFightingMessage::class);
			return;
		}

		$i = 1;
		do {
			$unit = null;
			try {
				$unit = $this->nextId($i, $id);
			} catch (CommandException) {
				$this->message(AttackNotFoundMessage::class)->p($id);
				continue;
			}
			if ($this->checkUnit($unit)) {
				$this->units[] = $unit;
			}
		} while ($unit);
		$this->commitCommand($this);
	}

	protected function run(): void {
		$campaign = $this->context->getCampaign($this->unit->Region());
		if ($campaign->mount()) {
			foreach ($campaign->Battles() as $battle) {
				//TODO battle between
				$battle->commence();
			}
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		if (!empty($this->units)) {
			$campaign = $this->context->getCampaign($this->unit->Region());
			foreach ($this->units as $unit) {
				$campaign->addAttack($this->unit, $unit);
				$this->message(AttackMessage::class)->e($unit);
				$this->message(AttackFromMessage::class, $unit)->e($this->unit->Party());
			}
			parent::commitCommand($command);
		}
	}

	private function checkUnit(?Unit $unit): bool {
		if (!$unit) {
			return false;
		}
		if ($unit === $this->unit) {
			$this->message(AttackSelfMessage::class);
			return false;
		}
		$party = $this->unit->Party();
		if ($unit->Party() === $party) {
			$this->message(AttackOwnUnitMessage::class)->e($unit);
			return false;
		}
		if ($unit->Region() !== $this->unit->Region()) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return false;
		}
		if (!$this->checkVisibility($this->calculus(), $unit)) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return false;
		}
		if ($party->Diplomacy()->has(Relation::COMBAT, $unit)) {
			$this->message(AttackAllyMessage::class)->e($unit);
			return false;
		}
		return true;
	}
}
