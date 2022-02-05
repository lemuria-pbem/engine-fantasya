<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoUnicumMessage;
use Lemuria\Model\Fantasya\Practice;

/**
 * This command is used to write to an Unicum.
 *
 * - SCHREIBEN <Unicum> ...
 * - SCHREIBEN <composition> <Unicum> ...
 */
final class Write extends UnitCommand
{
	use UnicumTrait;

	protected function run(): void {
		$id = $this->parseUnicum();
		if (!$this->unicum) {
			$this->message(WriteNoUnicumMessage::class)->p($id);
			return;
		}
		if ($this->unicum->Composition() !== $this->composition) {
			$this->message(WriteNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		$this->getOperate(Practice::WRITE)->write($this->phrase->getLine(3));
	}
}
