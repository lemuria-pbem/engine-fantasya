<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\LearnSpellAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\LearnSpellImpossibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\LearnSpellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\LearnSpellUnableMessage;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Talent\Magic;

trait LearnSpellTrait
{
	use MessageTrait;
	use WorkloadTrait;

	protected int $knowledge;

	protected int $production;

	/**
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function __construct(Context $context, Operator $operator) {
		parent::__construct($context, $operator);
		$this->knowledge = $this->context->getCalculus($operator->Unit())->knowledge(Magic::class)->Level();
		$this->initWorkload();
		$this->production = $this->reduceByWorkload($this->knowledge);
	}

	protected function learn(Spell $spell): void {
		$unit      = $this->operator->Unit();
		$spellBook = $unit->Party()->SpellBook();
		if ($spellBook[$spell]) {
			$this->message(LearnSpellAlreadyMessage::class, $unit)->s($spell);
			return;
		}
		$level = $spell->Difficulty();
		if ($level > $this->knowledge) {
			$this->message(LearnSpellImpossibleMessage::class, $unit)->s($spell);
			return;
		}
		if ($this->production < $level) {
			$this->message(LearnSpellUnableMessage::class, $unit)->s($spell);
			return;
		}
		$spellBook->add($spell);
		$this->addToWorkload($level);
		$this->message(LearnSpellMessage::class, $unit)->s($spell);
	}
}
