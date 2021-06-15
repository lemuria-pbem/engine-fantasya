<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\LearnOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnProgressMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnSilverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnTeachersMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent;

/**
 * Implementation of command LERNEN (a Unit learns a skill).
 *
 * The command increases the current unit's knowledge in one skill.
 *
 * - LERNEN <skill>
 */
final class Learn extends UnitCommand implements Activity
{
	use BuilderTrait;
	use OneActivityTrait;

	private Talent $talent;

	private ?Ability $progress = null;

	private Commodity $silver;

	private int $expense = 0;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->silver = self::createCommodity(Silver::class);
		$topic        = $this->phrase->getParameter();
		$this->talent = $this->context->Factory()->talent($topic);
		$this->calculus()->setStudent($this);
	}

	#[Pure] public function getTalent(): Talent {
		return $this->talent;
	}

	protected function initialize(): void {
		parent::initialize();
		$this->message(LearnTeachersMessage::class)->p(count($this->calculus()->getTeachers()));
		$calculus       = $this->calculus();
		$knowledge      = $this->unit->Knowledge();
		$ability        = $knowledge[$this->talent];
		$level          = $ability instanceof Ability ? $ability->Level() : 0;
		$this->progress = $calculus->progress($this->talent);
		$this->expense  = $this->unit->Size() * $this->talent->getExpense($level);
	}

	protected function run(): void {
		if (!$this->progress) {
			throw new LemuriaException('No progress initialized.');
		}

		if ($this->expense > 0) {
			$pool    = $this->context->getResourcePool($this->unit);
			$expense = $pool->reserve($this->unit, new Quantity($this->silver, $this->expense));
			$this->unit->Inventory()->remove($expense);
			$silver  = $expense->Count();
			if ($silver < $this->expense) {
				$experience = $this->progress->Experience();
				$progress   = (int)floor(($expense->Count() / $this->expense) * $experience);
				$this->progress->removeItem(new Ability($this->talent, $experience - $progress));
				$this->message(LearnOnlyMessage::class)->s($this->talent)->p($silver);
			} else {
				$this->message(LearnSilverMessage::class)->s($this->talent)->p($silver);
			}
		}

		$this->unit->Knowledge()->add($this->progress);
		foreach ($this->calculus()->getTeachers() as $teacher /** @var Teach $teacher */) {
			$teacher->hasTaught($this);
		}
		$this->message(LearnProgressMessage::class)->s($this->talent)->p($this->progress->Experience());
	}
}
