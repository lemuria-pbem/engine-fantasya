<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Command\Attack as AttackCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

/**
 * A monster attacks an enemy unit.
 */
class Attack implements Act
{
	use ActTrait;
	use MessageTrait;

	protected People $enemy;

	public function act(): Attack {
		if (!$this->enemy->isEmpty()) {
			$ids     = [];
			foreach ($this->enemy as $unit /* @var Unit $unit */) {
				$ids[] = (string)$unit->Id();
			}
			$state   = State::getInstance();
			$context = new Context($state);
			$context->setUnit($this->unit);
			$phrase = new Phrase('ATTACKIEREN ' . implode(' ', $ids));
			$attack = new AttackCommand($phrase, $context);
			$state->injectIntoTurn($attack);
		}
		return $this;
	}

	public function setEnemy(People $enemy): Attack {
		$this->enemy = $enemy;
		return $this;
	}
}
