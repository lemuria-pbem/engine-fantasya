<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\OperatorActivityTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteUnsupportedMessage;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Practice;

/**
 * This command is used to write to an Unicum.
 *
 * - SCHREIBEN <Unicum> ...
 * - SCHREIBEN <composition> <Unicum> ...
 */
final class Write extends UnitCommand implements Activity, Operator
{
	use OperatorActivityTrait;
	use UnicumTrait;

	public function Composition(): Composition {
		$this->parseUnicum();
		return $this->unicum->Composition();
	}

	protected function run(): void {
		$this->parseUnicumWithArguments();
		if (!$this->unicum) {
			$this->message(WriteNoUnicumMessage::class);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(WriteNoCompositionMessage::class)->s($this->composition)->p((string)$this->unicum->Id());
			return;
		}
		if ($composition->supports(Practice::WRITE)) {
			$this->getOperate(Practice::WRITE)->write();
		} else {
			$this->message(WriteUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}
}
