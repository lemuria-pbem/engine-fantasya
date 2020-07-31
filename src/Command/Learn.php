<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Message\Unit\LearnProgressMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LearnTeachersMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Model\Lemuria\Talent;

/**
 * Implementation of command LERNEN (a Unit learns a skill).
 *
 * The command increases the current unit's knowledge in one skill.
 *
 * - LERNEN <skill>
 */
final class Learn extends UnitCommand implements Activity
{
	private Talent $talent;

	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$topic        = $this->phrase->getParameter();
		$this->talent = $this->context->Factory()->talent($topic);
		$this->calculus()->setStudent($this);
	}

	/**
	 * Get the Talent.
	 *
	 * @return Talent
	 */
	public function getTalent(): Talent {
		return $this->talent;
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$this->message(LearnTeachersMessage::class)->e($this->unit)->p(count($this->calculus()->getTeachers()));
		$progress = $this->calculus()->progress($this->talent);
		$this->unit->Knowledge()->add($progress);
		$this->message(LearnProgressMessage::class)->e($this->unit)->s($this->talent)->p($progress->Experience());
	}
}
