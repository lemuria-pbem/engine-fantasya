<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\TakeNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeUnsupportedMessage;
use Lemuria\Model\Fantasya\Practice;

/**
 * This command is used to take an Unicum into possession.
 *
 * - NEHMEN <Unicum>
 * - NEHMEN <composition> <Unicum>
 */
final class Take extends UnitCommand implements Operator
{
	use UnicumTrait;

	protected function run(): void {
		$id = $this->findUnicum();
		if (!$this->unicum) {
			$this->message(TakeNoUnicumMessage::class)->p($id);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(TakeNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::TAKE)) {
			$this->getOperate(Practice::TAKE)->take();
		} else {
			$this->message(TakeUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}
}
