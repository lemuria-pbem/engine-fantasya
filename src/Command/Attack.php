<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\BattlePlan;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Engine\Fantasya\Combat\Place;
use Lemuria\Engine\Fantasya\Combat\Side;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Region\AttackBattleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackAllyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackCancelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackFromMonsterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackInBuildingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackInvolveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackLeaveConstructionCombatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackLeaveVesselCombatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOnVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackSelfMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Attacks units.
 *
 * - ATTACKIEREN <Unit>...
 */
final class Attack extends UnitCommand implements Reassignment
{
	use CamouflageTrait;
	use ReassignTrait;

	/**
	 * @var array<int, array>
	 */
	private static array $attackers = [];

	/**
	 * @var array<int, true>
	 */
	private static array $leavers = [];

	/**
	 * @var array<Unit>
	 */
	private array $units = [];

	public function from(Unit $unit): Attack {
		$this->unit = $unit;
		return $this;
	}

	protected function initialize(): void {
		parent::initialize();
		if ($this->unit->BattleRow()->value <= BattleRow::Bystander->value) {
			$this->message(AttackNotFightingMessage::class);
			parent::commitCommand($this);
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
			$place = $this->getPlace($unit);
			if ($place !== Place::None) {
				$this->units[] = $unit;
				Lemuria::Log()->debug($this->unit . ' will fight against ' . $unit . ' in ' . strtolower($place->name) . '.');
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
				Lemuria::Log()->debug('Beginning battle ' . ++$i . ' in region ' . $battle->Place()->Region() . '.');
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
		if ($party->Type() === Type::Player) {
			if (!self::isAttacked($id, $party->Id()->Id())) {
				$this->message(AttackFromMessage::class, $unit)->e($party);
			}
		} elseif (!self::isAttacked($id)) {
			$this->message(AttackFromMonsterMessage::class, $unit);
		}
	}

	private function getPlace(?Unit $unit): Place {
		if (!$unit) {
			return Place::None;
		}
		if ($unit === $this->unit) {
			$this->message(AttackSelfMessage::class);
			return Place::None;
		}
		$we    = $this->unit->Party();
		$party = $unit->Party();
		if ($party === $we) {
			$this->message(AttackOwnUnitMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if ($unit->Region() !== $this->unit->Region()) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		$isMonsterCombat = $we->Type() === Type::Monster && $party->Type() === Type::Monster;
		if (!$isMonsterCombat && !$this->checkVisibility($this->unit, $unit)) {
			$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if ($we->Diplomacy()->has(Relation::COMBAT, $unit)) {
			$this->message(AttackAllyMessage::class)->p((string)$unit->Id());
			return Place::None;
		}

		$place = BattlePlan::canAttack($this->unit, $unit);
		if ($place === Place::None) {
			$construction = $unit->Construction();
			if ($construction) {
				$this->message(AttackInBuildingMessage::class)->e($unit)->s($construction->Building());
			} else {
				$this->message(AttackOnVesselMessage::class)->e($unit);
			}
		} elseif ($place === Place::Region) {
			$id = $this->unit->Id()->Id();
			if (!isset(self::$leavers[$id])) {
				$construction = $this->unit->Construction();
				if ($construction) {
					self::$leavers[$id] = true;
					$this->message(AttackLeaveConstructionCombatMessage::class)->e($construction);
				} else {
					$vessel = $this->unit->Vessel();
					if ($vessel) {
						self::$leavers[$id] = true;
						$this->message(AttackLeaveVesselCombatMessage::class)->e($vessel);
					}
				}
			}
		}
		return $place;
	}
}
