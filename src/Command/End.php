<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\Unit\EndMessage;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Entity;

/**
 * Implementation of command ENDE (this should be the final command in a newly created unit's command block).
 *
 * The command resets the current Unit to the creator of the executing TEMP unit.
 *
 * - ENDE
 */
final class End extends UnitCommand implements Immediate
{
	public function skip(): static {
		$this->context->Parser()->skip(false);
		return $this;
	}

	protected function run(): void {
		$temp    = $this->context->UnitMapper()->find($this->unit);
		$creator = $temp->getCreator();
		$this->context->setUnit($creator);
		$this->message(EndMessage::class)->p($temp->getTempNumber());
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->context->Unit()->Id());
	}
}
