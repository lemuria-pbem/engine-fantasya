<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReadUnsupportedMessage;
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
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(ReadNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::READ)) {
			$this->getOperate(Practice::READ)->read();
		} else {
			$this->message(ReadUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}
}
