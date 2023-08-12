<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Message\Unit\LearnEffectivityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnFleetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnFleetNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnHasReachedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnMagicMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnProgressMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnReducedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnSilverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnTeachersMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnVesselMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Aura;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Alchemy;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Entertaining;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Shipbuilding;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Talent\Tactics;

/**
 * Implementation of command LERNEN (a Unit learns a skill).
 *
 * The command increases the current unit's knowledge in one skill.
 *
 * - LERNEN <skill>
 * - LERNEN <skill> <level>
 */
final class Learn extends UnitCommand implements Activity
{
	use BuilderTrait;
	use CollectTrait;
	use OneActivityTrait;
	use RealmTrait;
	use StatisticsTrait;

	public final const PROGRESS = 100;

	private const EFFECTIVITY = [
		''                => [
			Alchemy::class       => 0.5,  Archery::class      => 0.5,  Bladefighting::class => 0.75,
			Crossbowing::class   => 0.5,  Entertaining::class => 0.75, Magic::class        => 0.5,
			Navigation::class    => 1.0,  Perception::class   => 0.75, Shipbuilding::class => 0.5,
			Spearfighting::class => 0.75, Tactics::class      => 0.5
		],
		Boat::class       => [
			Entertaining::class => 0.2, Navigation::class => 0.2, Perception::class => 0.5
		],
		Longboat::class   => [
			Bladefighting::class => 0.2, Entertaining::class => 0.5, Spearfighting::class => 0.2,
			Navigation::class    => 0.5, Perception::class   => 0.5
		],
		Dragonship::class => [
			Archery::class       => 0.5,  Bladefighting::class => 0.75, Crossbowing::class => 0.5,
			Entertaining::class  => 0.75, Navigation::class    => 1.0,  Perception::class  => 0.75,
			Spearfighting::class => 0.75, Tactics::class       => 0.5
		]
	];

	private const FLEET_EXCEPTION = [Riding::class => true];

	private Talent $talent;

	private int $level = 0;

	private ?Ability $progress = null;

	private Commodity $silver;

	private int $expense = 0;

	private float $effectivity = 1.0;

	private float $fleetTime = 0.0;

	private bool $logCommit = false;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->silver = self::createCommodity(Silver::class);
		$topic        = $this->phrase->getParameter();
		$this->talent = $this->context->Factory()->talent($topic);
		$this->parseTalentLevel();
		$this->calculus()->addStudent($this);
	}

	public function canLearn(): bool {
		$result         = $this->progress(false);
		$this->progress = null;
		$this->expense  = 0;
		return $result;
	}

	public function getTalent(): Talent {
		return $this->talent;
	}

	public function getLevel(): int {
		return $this->level;
	}

	protected function initialize(): void {
		parent::initialize();
		$this->progress();
		if (!$this->checkSize() && $this->IsDefault()) {
			Lemuria::Log()->debug('Learning command skipped due to empty unit.', ['command' => $this]);
			return;
		}
		$this->logCommit = true;
		$this->commitCommand($this);
	}

	protected function run(): void {
		if (!$this->progress) {
			$this->message(LearnHasReachedMessage::class)->s($this->talent)->p($this->level);
			return;
		}

		if ($this->effectivity > 0.0) {
			if ($this->expense > 0) {
				$expense = $this->collectQuantity($this->unit, $this->silver, $this->expense);
				$silver  = $expense->Count();
				if ($silver <= 0) {
					$this->message(LearnNotMessage::class)->s($this->talent);
					return;
				}

				$this->unit->Inventory()->remove($expense);
				$this->placeDataMetrics(Subject::LearningCosts, $silver, $this->unit);
				if ($silver < $this->expense) {
					$experience = $this->progress->Experience();
					$progress   = (int)floor(($expense->Count() / $this->expense) * $experience);
					$this->progress->removeItem(new Ability($this->talent, $experience - $progress));
					$this->message(LearnOnlyMessage::class)->s($this->talent)->p($silver);
				} else {
					$this->message(LearnSilverMessage::class)->s($this->talent)->p($silver);
				}
			}

			$oldLevel = $this->calculus()->knowledge($this->talent)->Level();

			$this->unit->Knowledge()->add($this->progress);
			foreach ($this->calculus()->getTeachers() as $teacher) {
				$teacher->hasTaught($this);
			}
			if ($this->effectivity < 1.0) {
				$ship = $this->unit->Vessel()->Ship();
				$this->message(LearnEffectivityMessage::class)->p($this->effectivity);
				$this->message(LearnReducedMessage::class)->s($this->talent)->p($this->progress->Experience())->s($ship, LearnReducedMessage::SHIP);
			} elseif ($this->fleetTime > 0.0) {
				if ($this->fleetTime < 1.0) {
					$this->message(LearnFleetMessage::class)->s($this->talent)->p($this->progress->Experience());
				} else {
					$this->message(LearnFleetNothingMessage::class)->s($this->talent);
				}
			} else {
				$this->message(LearnProgressMessage::class)->s($this->talent)->p($this->progress->Experience());
			}

			if ($this->talent instanceof Magic) {
				$newLevel = $this->calculus()->knowledge($this->talent)->Level();
				if ($newLevel > 0 && $newLevel > $oldLevel) {
					$aura     = $this->unit->Aura() ?? new Aura();
					$addition = $newLevel ** 2 - $oldLevel ** 2;
					$aura->setMaximum($aura->Maximum() + $addition);
					$this->unit->setAura($aura);
					$this->message(LearnMagicMessage::class)->p($addition);
				}
			}
		} else {
			$ship = $this->unit->Vessel()->Ship();
			$this->message(LearnVesselMessage::class)->s($this->talent)->s($ship, LearnVesselMessage::SHIP);
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		if ($this->progress) {
			parent::commitCommand($command);
		} elseif ($this->logCommit) {
			$this->context->getProtocol($this->unit)->logCurrent($command);
		}
	}

	private function parseTalentLevel(): void {
		if ($this->phrase->count() === 2) {
			$parameter = $this->phrase->getParameter(2);
			$level     = (int)$parameter;
			if ($level > 0 && (string)$level === $parameter) {
				$this->level = $level;
			} else {
				throw new InvalidCommandException($this);
			}
		}
	}

	private function progress(bool $withMessage = true): bool {
		if (!$this->checkSize() && $this->IsDefault()) {
			return false;
		}

		if ($this->isRunCentrally($this) && !isset(self::FLEET_EXCEPTION[$this->talent::class])) {
			$realm           = $this->unit->Region()->Realm();
			$this->fleetTime = $this->context->getRealmFleet($realm)->getUsedCapacity($this->unit);
		}

		$calculus  = $this->calculus();
		$knowledge = $this->unit->Knowledge();
		$ability   = $knowledge[$this->talent];
		$level     = $ability instanceof Ability ? $ability->Level() : 0;
		if ($this->level <= 0 || $level < $this->level) {
			if ($withMessage) {
				$this->message(LearnTeachersMessage::class)->p(count($this->calculus()->getTeachers()));
			}
			$this->progress = $calculus->progress($this->talent, (1.0 - $this->fleetTime) * $this->effectivity());
			$this->expense  = (int)round((1.0 - $this->fleetTime) * $this->unit->Size() * $this->talent->getExpense($level));
			return true;
		}
		return false;
	}

	private function effectivity(): float {
		if ($this->hasTravelled()) {
			$ship = $this->unit->Vessel()?->Ship();
			if ($ship) {
				$class = $ship::class;
				if (!isset(self::EFFECTIVITY[$class])) {
					$class = '';
				}
				$this->effectivity = self::EFFECTIVITY[$class][$this->talent::class] ?? 0.0;
			}
		}
		return $this->effectivity;
	}

	private function hasTravelled(): bool {
		$effect = new TravelEffect(State::getInstance());
		return Lemuria::Score()->find($effect->setUnit($this->unit)) instanceof TravelEffect;
	}
}
