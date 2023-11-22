<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SummonBeingMessage;
use Lemuria\Engine\Fantasya\Effect\DissolveEffect;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Monster\AirElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\EarthElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\FireElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\WaterElemental;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Unit;

class ElementalBeing extends AbstractBattleSpell
{
	/**
	 * @type array<string>
	 */
	private const array ELEMENTALS = [AirElemental::class, EarthElemental::class, FireElemental::class, WaterElemental::class];

	private static ?array $races = null;

	public function __construct(BattleSpellGrade $grade) {
		parent::__construct($grade);
		if (!self::$races) {
			self::$races = [];
			foreach (self::ELEMENTALS as $class) {
				$elemental = self::createMonster($class);
				foreach ($elemental->Environment() as $landscape) {
					self::$races[$landscape::class] = $elemental;
				}
			}
		}
	}

	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$unit      = $this->calculus->Unit();
			$party     = $unit->Party();
			$region    = $unit->Region();
			$landscape = $region->Landscape();
			$race      = self::$races[$landscape::class];
			$create    = new Create($party, $region);
			$create->add(new Gang($race));
			$elemental = $create->act()->getUnits()[0];
			$effect    = new DissolveEffect(State::getInstance());
			Lemuria::Score()->add($effect->setUnit($elemental));

			$combatant                               = $this->createCombatant($unit, $elemental);
			$this->caster[BattleRow::Front->value][] = $combatant;
			BattleLog::getInstance()->add(new SummonBeingMessage(new Entity($elemental), $combatant, $unit->Size(), BattleRow::Front->value));
			Lemuria::Log()->debug('New combatant ' . $combatant->Id() . ' for party ' . $party . ' consisting of one ' . $race . ' has been summoned.');
		}
		return $grade;
	}

	private function createCombatant(Unit $unit, Unit $elemental): Combatant {
		$distribution = new Distribution();
		$distribution->setSize($elemental->Size());
		$army      = $this->grade->Combat()->getArmy($unit);
		$combatant = new Combatant($army, $elemental);
		$combatant->setBattleRow(BattleRow::Front);
		$combatant->setDistribution($distribution);
		$army->addCombatant($combatant);
		return $combatant;
	}
}
