<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\LearnProgressMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LearnTeachersMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Ability;
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
	use OneActivityTrait;

	private Talent $talent;

	private ?Ability $progress = null;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
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
		$this->progress = $this->calculus()->progress($this->talent);
	}

	protected function run(): void {
		if (!$this->progress) {
			throw new LemuriaException('No progress initialized.');
		}

		$this->unit->Knowledge()->add($this->progress);
		$this->message(LearnProgressMessage::class)->s($this->talent)->p($this->progress->Experience());
	}
}
