<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Talent\Magic;

trait LearnSpellTrait
{
	use WorkloadTrait;

	protected int $knowledge;

	public function __construct(Context $context, Operator $operator) {
		parent::__construct($context, $operator);
		$this->knowledge = $this->context->getCalculus($operator->Unit())->knowledge(Magic::class)->Level();
		$this->initWorkload();
		$this->addToWorkload($this->knowledge);
	}

	protected function learn(Spell $spell): void {
		$unit      = $this->operator->Unit();
		$spellBook = $unit->Party()->SpellBook();
		if ($spellBook[$spell]) {
			//TODO already
			return;
		}
		$level = $spell->Difficulty();
		if ($level > $this->knowledge) {
			//TODO too high
			return;
		}
		if ($this->reduceByWorkload($level) < $level) {
			//TODO cannot learn
			return;
		}
		//TODO learn
		return;
	}
}
