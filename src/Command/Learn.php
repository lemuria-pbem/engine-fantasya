<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
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
use Lemuria\Exception\SingletonException;
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

	public final const int PROGRESS = 100;

	/**
	 * @type array<string, array<string, float>>
	 */
	private const array EFFECTIVITY = [
		''                => [
			Alchemy::class       => 0.5, Archery::class      => 0.5, Bladefighting::class => 0.8,
			Crossbowing::class   => 0.5, Entertaining::class => 0.8, Magic::class         => 0.5,
			Navigation::class    => 1.0, Perception::class   => 0.8, Shipbuilding::class  => 0.5,
			Spearfighting::class => 0.8, Tactics::class      => 0.5
		],
		Boat::class       => [
			                             Entertaining::class => 0.2,
			Navigation::class    => 0.2, Perception::class   => 0.5
		],
		Longboat::class   => [
			Alchemy::class       => 0.2,                             Bladefighting::class => 0.2,
			                             Entertaining::class => 0.5,
			Navigation::class    => 0.5, Perception::class   => 0.5, Shipbuilding::class  => 0.2,
			Spearfighting::class => 0.2
		],
		Dragonship::class => [
			Alchemy::class       => 0.2, Archery::class      => 0.2, Bladefighting::class => 0.5,
			Crossbowing::class   => 0.2, Entertaining::class => 0.5, Magic::class         => 0.2,
			Navigation::class    => 1.0, Perception::class   => 0.8, Shipbuilding::class  => 0.5,
			Spearfighting::class => 0.5, Tactics::class      => 0.2
		]
	];

	/**
	 * @type array<string, true>
	 */
	private const array FLEET_EXCEPTION = [Riding::class => true];

	private Calculus $calculus;

	private ?Talent $talent = null;

	private int $level = 0;

	private ?Ability $progress = null;

	private Commodity $silver;

	private int $expense = 0;

	private float $effectivity = 1.0;

	private float $fleetTime = 0.0;

	private bool $logCommit = false;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->calculus = $this->calculus();
		$this->silver   = self::createCommodity(Silver::class);
		$this->parseTalent();
		$this->parseTalentLevel();
		$this->calculus->addStudent($this);
	}

	public function canLearn(): bool {
		if (!$this->talent || !$this->context->getProtocol($this->unit)->isAllowed($this)) {
			return false;
		}
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
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2 || !$this->talent || $n === 2 && !$this->level) {
			throw new InvalidCommandException($this);
		}
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
			$this->preventDefault();
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

			$oldLevel = $this->calculus->ability($this->talent)->Level();

			$this->unit->Knowledge()->add($this->progress);
			foreach ($this->calculus->getTeachers() as $teacher) {
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
				$newLevel = $this->calculus->ability($this->talent)->Level();
				if ($newLevel > 0 && $newLevel > $oldLevel) {
					$aura     = $this->unit->Aura() ?? new Aura();
					$addition = $newLevel ** 2 - $oldLevel ** 2;
					$aura->setMaximum($aura->Maximum() + $addition);
					$this->unit->setAura($aura);
					$this->message(LearnMagicMessage::class)->p($addition);
				}
			}
			if ($this->hasReachedLevel()) {
				$this->context->getProtocol($this->unit)->replaceDefaults($this->preventDefault());
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

	private function parseTalent(): void {
		if ($this->phrase->count() > 0) {
			try {
				$this->talent = $this->context->Factory()->talent($this->phrase->getParameter());
			} catch (SingletonException|UnknownItemException) {
			}
		}
	}

	private function parseTalentLevel(): void {
		if ($this->phrase->count() >= 2) {
			$parameter = $this->phrase->getParameter(2);
			$level     = (int)$parameter;
			if ($level > 0 && (string)$level === $parameter) {
				$this->level = $level;
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

		$level = $this->calculus->ability($this->talent)->Level();
		if ($this->level <= 0 || $level < $this->level) {
			if ($withMessage) {
				$this->message(LearnTeachersMessage::class)->p(count($this->calculus->getTeachers()));
			}
			$this->progress = $this->calculus->progress($this->talent, (1.0 - $this->fleetTime) * $this->effectivity());
			$knowledge      = $this->unit->Knowledge();
			$costLevel      = $knowledge[$this->talent] instanceof Ability ? $knowledge[$this->talent]->Level() : 0;
			$this->expense  = (int)round((1.0 - $this->fleetTime) * $this->unit->Size() * $this->talent->getExpense($costLevel));
			return true;
		}
		return false;
	}

	private function hasReachedLevel(): bool {
		if ($this->level <= 0) {
			return false;
		}
		$level = $this->calculus->ability($this->talent)->Level();
		return $level >= $this->level;
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
