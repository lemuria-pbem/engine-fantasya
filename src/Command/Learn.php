<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Message\Unit\LearnProgressMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LearnTeachersMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Lemuria\Ability;
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

	private ?Ability $progress = null;

	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 * @param Context $context
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
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		$this->message(LearnTeachersMessage::class)->p(count($this->calculus()->getTeachers()));
		$this->progress = $this->calculus()->progress($this->talent);
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if (!$this->progress) {
			throw new LemuriaException('No progress initialized.');
		}

		$this->unit->Knowledge()->add($this->progress);
		$this->message(LearnProgressMessage::class)->s($this->talent)->p($this->progress->Experience());
	}
}
