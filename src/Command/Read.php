<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoUnicumMessage;
use Lemuria\Model\Fantasya\Practice;

/**
 * This command is used to ask for information about an Unicum.
 *
 * - LESEN <Unicum>
 * - LESEN <composition> <Unicum>
 * - UNTERSUCHEN <Unicum>
 * - UNTERSUCHEN <composition> <Unicum>
 */
final class Read extends UnitCommand implements Operator
{
	use UnicumTrait;

	protected function run(): void {
		$id = $this->parseUnicum();
		if (!$this->unicum) {
			$this->message(ReadNoUnicumMessage::class)->p($id);
			return;
		}
		if ($this->unicum->Composition() !== $this->composition) {
			$this->message(ReadNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}

		$this->getOperate(Practice::READ)->read();
	}
}
