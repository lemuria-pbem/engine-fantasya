<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Combat\BattlePlan;
use Lemuria\Engine\Fantasya\Combat\Place;
use Lemuria\Engine\Fantasya\Combat\Side;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\InvisibleEnemy;
use Lemuria\Engine\Fantasya\Effect\NonAggressionPact;
use Lemuria\Engine\Fantasya\Exception\Command\InvalidIdException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Entity;
use Lemuria\Exception\IdException;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;
use Lemuria\Singleton;

/**
 * Attacks other units.
 *
 * - ATTACKIEREN Monster
 * - ATTACKIEREN <race>
 * - ATTACKIEREN <unit>...
 * - ATTACKIEREN Partei <party>...
 */
abstract class AssaultCommand extends UnitCommand implements Reassignment
{
	use CamouflageTrait;
	use ReassignTrait;

	/**
	 * @var array<int, array>
	 */
	protected static array $attackers = [];

	/**
	 * @var array<int, true>
	 */
	protected static array $leavers = [];

	/**
	 * @var array<int, Region>|null
	 */
	protected static ?array $resetCampaign = [];

	protected People $units;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->units = new People();
	}

	protected function initialize(): void {
		parent::initialize();
		if (!$this->checkSize()) {
			$this->emptyMessage('Empty');
			//$this->message(AttackEmptyMessage::class);
			return;
		}
		if ($this->unit->BattleRow()->value <= BattleRow::Bystander->value) {
			$this->emptyMessage('NotFighting');
			//$this->message(AttackNotFightingMessage::class);
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

	protected function commitCommand(UnitCommand $command): void {
		if (!$this->units->isEmpty()) {
			$campaign = $this->context->getCampaign($this->unit->Region());
			foreach ($this->units as $unit) {
				$side = $campaign->addAttack($this->unit, $unit);
				switch ($side) {
					case Side::Attacker :
						$this->entityMessage('', $unit);
						//$this->message(AttackMessage::class)->e($unit);
						$this->addAttackFromMessage($unit);
						break;
					case Side::Involve :
						$this->entityMessage('Involve', $unit);
						//$this->message(AttackInvolveMessage::class)->e($unit);
						$this->addAttackFromMessage($unit);
						break;
					case Side::Defender :
						$this->entityMessage('Cancel', $unit);
						//$this->message(AttackCancelMessage::class)->e($unit);
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
					$this->emptyMessage('OwnParty');
					//$this->message(AttackOwnPartyMessage::class);
					continue;
				}
				if ($we->Diplomacy()->has(Relation::COMBAT, $party)) {
					$this->parameterMessage('AlliedParty', (string)$party->Id());
					//$this->message(AttackAlliedPartyMessage::class)->p((string)$party->Id());
				} else {
					$parties->add($party);
				}
			} catch (IdException $e) {
				throw new InvalidIdException($this->phrase->getParameter($i), $e);
			} catch (NotRegisteredException) {
				$verb = strtolower(getClass($this));
				/** @noinspection PhpUndefinedVariableInspection */
				Lemuria::Log()->debug('Party ' . $we . ' tried to ' . $verb . ' non-existing party ' . $id . '.');
			}
		}

		$outlook = new Outlook(new Census($we));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit) {
			$party = $this->getApparentParty($unit);
			if ($party && $parties->has($party->Id())) {
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
				$this->parameterMessage('NotFound', $id);
				//$this->message(AttackNotFoundMessage::class)->p($id);
			}
		}
	}

	private function addAttackedUnit(Unit $unit): void {
		if ($unit->Size() > 0) {
			$place = $this->getPlace($unit);
			if ($place !== Place::None) {
				$this->units->add($unit);
				Lemuria::Log()->debug($this->unit . ' will fight against ' . $unit . ' in ' . strtolower($place->name) . '.');
			}
		} else {
			Lemuria::Log()->debug('Attacked unit ' . $unit . ' is empty, skipped.');
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
		if ($party->Type() !== Type::Monster) {
			if (!self::isAttacked($id, $party->Id()->Id())) {
				$this->entityMessage('From', $party, $unit);
				//$this->message(AttackFromMessage::class, $unit)->e($party);
			}
		} elseif (!self::isAttacked($id)) {
			$this->emptyMessage('FromMonster', $unit);
			//$this->message(AttackFromMonsterMessage::class, $unit);
		}
	}

	private function getPlace(?Unit $unit): Place {
		if (!$unit) {
			return Place::None;
		}
		if ($unit === $this->unit) {
			$this->emptyMessage('Self');
			//$this->message(AttackSelfMessage::class);
			return Place::None;
		}
		$we    = $this->unit->Party();
		$party = $this->getApparentParty($unit);
		if ($party === $we) {
			$this->parameterMessage('OwnUnit', (string)$unit->Id());
			//$this->message(AttackOwnUnitMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if ($this->isProtectedFromAttacks($party)) {
			$this->parameterMessage('ProtectedParty', (string)$unit->Id());
			//$this->message(AttackProtectedPartyMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		$region = $unit->Region();
		if ($region !== $this->unit->Region()) {
			$this->parameterMessage('NotFound', (string)$unit->Id());
			//$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if (!$this->checkAttackVisibility($this->unit, $unit)) {
			$this->addInvisibleEnemyEffect($unit);
			$this->parameterMessage('NotFound', (string)$unit->Id());
			//$this->message(AttackNotFoundMessage::class)->p((string)$unit->Id());
			return Place::None;
		}
		if ($we->Diplomacy()->has(Relation::COMBAT, $unit)) {
			$this->parameterMessage('Ally', (string)$unit->Id());
			//$this->message(AttackAllyMessage::class)->p((string)$unit->Id());
			return Place::None;
		}

		$place = BattlePlan::canAttack($this->unit, $unit);
		if ($place === Place::None) {
			$construction = $unit->Construction();
			if ($construction) {
				$this->entitySingletonMessage('InBuilding', $unit, $construction->Building());
				//$this->message(AttackInBuildingMessage::class)->e($unit)->s($construction->Building());
			} else {
				$this->entityMessage('OnVessel', $unit);
				//$this->message(AttackOnVesselMessage::class)->e($unit);
			}
		} elseif ($place === Place::Region) {
			$id = $this->unit->Id()->Id();
			if (!isset(self::$leavers[$id])) {
				$construction = $this->unit->Construction();
				if ($construction) {
					self::$leavers[$id] = true;
					$this->entityMessage('LeaveConstructionCombat', $construction);
					//$this->message(AttackLeaveConstructionCombatMessage::class)->e($construction);
				} else {
					$vessel = $this->unit->Vessel();
					if ($vessel && !($region->Landscape() instanceof Navigable)) {
						self::$leavers[$id] = true;
						$this->entityMessage('LeaveVesselCombat', $vessel);
						//$this->message(AttackLeaveVesselCombatMessage::class)->e($vessel);
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

	private function addInvisibleEnemyEffect(Unit $unit): void {
		$effect   = new InvisibleEnemy(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof InvisibleEnemy) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->From()->add($this->unit);
		Lemuria::Log()->debug($unit . ' added as invisible enemy of ' . $this->unit . '.');
	}

	private function emptyMessage(string $name, ?Entity $target = null): void {
		$class = $target ? $this->getMessageClass($name, $target->Catalog()) : $this->getMessageClass($name);
		$this->message($class, $target);
	}

	private function parameterMessage(string $name, string $parameter, ?Entity $target = null): void {
		$class = $target ? $this->getMessageClass($name, $target->Catalog()) : $this->getMessageClass($name);
		$this->message($class, $target)->p($parameter);
	}

	private function entityMessage(string $name, Entity $entity, ?Entity $target = null): void {
		$class = $target ? $this->getMessageClass($name, $target->Catalog()) : $this->getMessageClass($name);
		$this->message($class, $target)->e($entity);
	}

	private function entitySingletonMessage(string $name, Entity $entity, Singleton $singleton, ?Entity $target = null): void {
		$class = $target ? $this->getMessageClass($name, $target->Catalog()) : $this->getMessageClass($name);
		$this->message($class, $target)->e($entity)->s($singleton);
	}

	private function getMessageClass(string $name, Domain $domain = Domain::Unit): string {
		$class = 'Lemuria\\Engine\\Fantasya\\Message\\' . $domain->name . '\\' . getClass($this) . $name . 'Message';
		if (!class_exists($class)) {
			throw new LemuriaException('Message class ' . $class . ' not found.');
		}
		return $class;
	}
}
