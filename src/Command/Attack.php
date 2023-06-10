<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\BattlePlan;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Engine\Fantasya\Combat\Place;
use Lemuria\Engine\Fantasya\Combat\Side;
use Lemuria\Engine\Fantasya\Effect\NonAggressionPact;
use Lemuria\Engine\Fantasya\Exception\Command\InvalidIdException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Region\AttackBattleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackAlliedPartyMessage;
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
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackOwnUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackProtectedPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AttackSelfMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Attacks other units.
 *
 * - ATTACKIEREN Monster
 * - ATTACKIEREN <race>
 * - ATTACKIEREN <unit>...
 * - ATTACKIEREN Partei <party>...
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

		$n         = $this->phrase->count();
		$parameter = strtolower($this->phrase->getParameter());
		if ($n === 1) {
			if ($parameter === 'monster') {
				$this->addAttackedMonsters();
			} elseif (!$this->addAttackedRace($parameter)) {
				$this->parseAttackedUnits();
			}
		} elseif ($n >= 2 && $parameter === 'partei') {
			$this->parseAttackedParties();
		} else {
			$this->parseAttackedUnits();
		}
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

	private function addAttackedMonsters(): void {
		$we      = $this->unit->Party();
		$outlook = new Outlook(new Census($we));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit) {
			$party = $this->getApparentParty($unit);
			if ($party->Type() === Type::Monster && $party !== $we) {
				$this->addAttackedUnit($unit);
			}
		}
	}

	private function addAttackedRace(string $parameter): bool {
		$race = $this->context->Factory()->parseRace($parameter);
		if (!$race) {
			return false;
		}

		$we      = $this->unit->Party();
		$outlook = new Outlook(new Census($we));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit) {
			if ($unit->Race() === $race && $unit->Party() !== $we) {
				$this->addAttackedUnit($unit);
			}
		}
		return true;
	}

	private function parseAttackedParties(): void {
		$parties = new Gathering();
		$we      = $this->unit->Party();
		$n       = $this->phrase->count();
		for ($i = 2; $i <= $n; $i++) {
			try {
				$id    = Id::fromId($this->phrase->getParameter($i));
				$party = Party::get($id);
				if ($party === $we) {
					$this->message(AttackOwnPartyMessage::class);
					continue;
				}
				if ($we->Diplomacy()->has(Relation::COMBAT, $party)) {
					$this->message(AttackAlliedPartyMessage::class)->p((string)$party->Id());
				} else {
					$parties->add($party);
				}
			} catch (IdException $e) {
				throw new InvalidIdException($this->phrase->getParameter($i), $e);
			} catch (NotRegisteredException) {
				Lemuria::Log()->debug('Party ' . $we . ' tried to attack non-existing party ' . $id . '.');
			}
		}

		$outlook = new Outlook(new Census($we));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit) {
			$party = $this->getApparentParty($unit);
			if ($parties->has($party->Id())) {
				$this->addAttackedUnit($unit);
			}
		}
	}

	private function parseAttackedUnits(): void {
		$i = 1;
		$n = $this->phrase->count();
		while ($i <= $n) {
			$unit = null;
			try {
				$unit = $this->nextId($i, $id);
			} catch (CommandException) {
			}
			if ($unit) {
				$this->addAttackedUnit($unit);
			} else {
				$this->message(AttackNotFoundMessage::class)->p($id);
			}
		}
	}

	private function addAttackedUnit(Unit $unit): void {
		$place = $this->getPlace($unit);
		if ($place !== Place::None) {
			$this->units[] = $unit;
			Lemuria::Log()->debug($this->unit . ' will fight against ' . $unit . ' in ' . strtolower($place->name) . '.');
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
		$party = $this->getApparentParty($unit);
		if ($party === $we) {
			$this->message(AttackOwnUnitMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if ($this->isProtectedFromAttacks($party)) {
			$this->message(AttackProtectedPartyMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		$region = $unit->Region();
		if ($region !== $this->unit->Region()) {
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
					if ($vessel && !($region->Landscape() instanceof Navigable)) {
						self::$leavers[$id] = true;
						$this->message(AttackLeaveVesselCombatMessage::class)->e($vessel);
					}
				}
			}
		}
		return $place;
	}

	private function getApparentParty(Unit $unit): ?Party {
		$party = $unit->Disguise();
		if ($party === $this->unit->Party()) {
			return null;
		}
		return $party === false ? $unit->Party() : $party;
	}

	private function isProtectedFromAttacks(Party $party): bool {
		$effect = new NonAggressionPact(State::getInstance());
		return (bool)Lemuria::Score()->find($effect->setParty($party));
	}
}
