<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\UnitTrait;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Lemuria;
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

	/**
	 * Execute the command.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		parent::execute();
		$protocol = $this->context->getProtocol($this->unit);
		if (!$protocol->commit($this)) {
			throw new ActivityException($this);
		}
		return $this;
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
