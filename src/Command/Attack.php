<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Message\Region\AttackBattleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackAllyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMonsterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackSelfMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Party;
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
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}

		$region   = $this->unit->Region();
		$campaign = $this->context->getCampaign($region);
		if ($campaign->mount()) {
			$i = 0;
			foreach ($campaign->Battles() as $battle) {
				Lemuria::Log()->debug('Beginning battle ' . ++$i . ' in region ' . $battle->Region() . '.');
				$attacker = [];
				foreach ($battle->Attacker() as $party) {
					$attacker[] = $party->Name();
				}
				$defender = [];
				foreach ($battle->Defender() as $party) {
					$defender[] = $party->Name();
				}
				$this->message(AttackBattleMessage::class, $region)->p($attacker, AttackBattleMessage::ATTACKER)->p($defender, AttackBattleMessage::DEFENDER);

				$log = new BattleLog($battle);
				BattleLog::init($log)->add(new BattleBeginsMessage($battle));
				$battle->commence($this->context);
				Lemuria::Hostilities()->add($log);
			}
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		if (!empty($this->units)) {
			$campaign = $this->context->getCampaign($this->unit->Region());
			foreach ($this->units as $unit) {
				$campaign->addAttack($this->unit, $unit);
				$this->message(AttackMessage::class)->e($unit);
				$party = $this->unit->Party();
				if ($party->Type() === Party::PLAYER) {
					$this->message(AttackFromMessage::class, $unit)->e($this->unit->Party());
				} else {
					$this->message(AttackFromMonsterMessage::class, $unit);
				}
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
		if (!$this->checkVisibility($this->unit, $unit)) {
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
