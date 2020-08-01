<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\Unit\EndMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

/**
 * Implementation of command ENDE (this should be the final command in a newly created unit's command block).
 *
 * The command resets the current Unit to the creator of the executing TEMP unit.
 *
 * - ENDE
 */
final class End extends UnitCommand implements Immediate
{
	/**
	 * Skip the command.
	 *
	 * @return Immediate
	 */
	public function skip(): Immediate {
		$this->context->Parser()->skip(false);
		return $this;
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$temp    = $this->context->UnitMapper()->find($this->unit);
		$creator = $temp->getCreator();
		$this->context->setUnit($creator);
		$this->message(EndMessage::class)->p($temp->getTempNumber());
	}

	/**
	 * @param LemuriaMessage $message
	 * @return LemuriaMessage
	 */
	protected function initMessage(LemuriaMessage $message): LemuriaMessage {
		return $message->e($this->context->Unit());
	}
}
