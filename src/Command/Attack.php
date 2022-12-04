<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Engine\Fantasya\Combat\Side;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Message\Region\AttackBattleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackAllyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackCancelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMonsterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackInCastleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackInvolveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOnVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackSelfMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
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
	 * @var array(int=>array)
	 */
	private static array $attackers = [];

	/**
	 * @var Unit[]
	 */
	private array $units = [];

	public function from(Unit $unit): Attack {
		$this->unit = $unit;
		return $this;
	}

	protected function initialize(): void {
		parent::initialize();
		if ($this->unit->BattleRow() <= BattleRow::BYSTANDER) {
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
				foreach ($battle->Attacker() as $party /* @var Party $party */) {
					$attacker[] = $party->Name();
				}
				$defender = [];
				foreach ($battle->Defender() as $party /* @var Party $party */) {
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
				$side = $campaign->addAttack($this->unit, $unit);
				switch ($side) {
					case Side::Attacker :
						$this->message(AttackMessage::class)->e($unit);
						$this->addAttackFromMessage($unit);
						break;
					case Side::Involve :
						$this->message(AttackInvolveMessage::class)->e($unit);
						$this->addAttackFromMessage($unit);
						break;
					case Side::Defender :
						$this->message(AttackCancelMessage::class)->e($unit);
				}
			}
			parent::commitCommand($command);
		}
	}

	private static function isAttacked(int $id, int $from = 0): bool {
		if (isset(self::$attackers[$id][$from])) {
			return true;
		}
		self::$attackers[$id][$from] = true;
		return false;
	}

	private function addAttackFromMessage(Unit $unit): void {
		$id    = $unit->Id()->Id();
		$party = $this->unit->Party();
		if ($party->Type() === Type::PLAYER) {
			if (!self::isAttacked($id, $party->Id()->Id())) {
				$this->message(AttackFromMessage::class, $unit)->e($party);
			}
		} elseif (!self::isAttacked($id)) {
			$this->message(AttackFromMonsterMessage::class, $unit);
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
		$we    = $this->unit->Party();
		$party = $unit->Party();
		if ($party === $we) {
			$this->message(AttackOwnUnitMessage::class)->p((string)$unit->Id());
			return false;
		}
		if ($unit->Region() !== $this->unit->Region()) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return false;
		}
		$isMonsterCombat = $we->Type() === Type::MONSTER && $party->Type() === Type::MONSTER;
		if (!$isMonsterCombat && !$this->checkVisibility($this->unit, $unit)) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return false;
		}
		if ($we->Diplomacy()->has(Relation::COMBAT, $unit)) {
			$this->message(AttackAllyMessage::class)->p((string)$unit->Id());
			return false;
		}

		$construction    = $unit->Construction();
		$ourConstruction = $this->unit->Construction();
		if ($construction instanceof Castle && $construction !== $ourConstruction) {
			$this->message(AttackInCastleMessage::class)->e($unit);
			return false;
		}
		$vessel    = $unit->Vessel();
		$ourVessel = $this->unit->Vessel();
		if ($vessel && $vessel !== $ourVessel) {
			$this->message(AttackOnVesselMessage::class)->e($unit);
			return false;
		}

		if (!$construction && !$vessel) {
			if ($ourConstruction) {
				$this->message(LeaveConstructionMessage::class)->e($ourConstruction);
			} elseif ($ourVessel) {
				$this->message(LeaveVesselMessage::class)->e($ourVessel);
			}
		}
		return true;
	}
}
