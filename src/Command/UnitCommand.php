<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Calculus;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Exception\ActivityException;
use Lemuria\Engine\Lemuria\Factory\UnitTrait;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Entity;
use Lemuria\Model\Lemuria\Unit;

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
		$protocol = $this->context->getProtocol($this->unit);
		if (!$protocol->commit($this)) {
			throw new ActivityException($this);
		}
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
