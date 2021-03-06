<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\UnitTrait;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Base class for all unit commands.
 */
abstract class UnitCommand extends AbstractCommand
{
	use UnitTrait;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->unit = $context->Unit();
	}

	public function isPrepared(): bool {
		if ($this instanceof Activity && $this->IsDefault() && $this->unit->Size() <= 0) {
			return false;
		}
		return parent::isPrepared();
	}

	/**
	 * Get command as string.
	 */
	#[Pure] public function __toString(): string {
		return '[' . $this->unit->Id() . '] ' . parent::__toString();
	}

	#[Pure] public function Phrase(): Phrase {
		return $this->phrase;
	}

	#[Pure] public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		$this->context->setUnit($this->unit);
		$this->commitCommand($this);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($target ? $target->Id() : $this->unit->Id());
	}

	/**
	 * Get the calculus.
	 */
	protected function calculus(): Calculus {
		return $this->context->getCalculus($this->unit);
	}
}
