<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Calculus;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Exception\ActivityException;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Model\Lemuria\Unit;

/**
 * Base class for all unit commands.
 */
abstract class UnitCommand extends AbstractCommand
{
	protected Unit $unit;

	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 * @param Context $context
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->unit = $context->Unit();
	}

	/**
	 * Get command as string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return '[' . $this->unit->Id() . '] ' . parent::__toString();
	}

	/**
	 * Get the unit.
	 *
	 * @return Unit
	 */
	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function prepare(): void {
		$protocol = $this->context->getProtocol($this->unit);
		if (!$protocol->commit($this)) {
			throw new ActivityException($this);
		}
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function initMessage(LemuriaMessage $message): LemuriaMessage {
		return $message->e($this->unit);
	}

	/**
	 * Get the calculus.
	 *
	 * @return Calculus
	 */
	protected function calculus(): Calculus {
		return $this->context->getCalculus($this->unit);
	}
}
