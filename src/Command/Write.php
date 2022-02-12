<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteUnsupportedMessage;
use Lemuria\Model\Fantasya\Practice;

/**
 * This command is used to write to an Unicum.
 *
 * - SCHREIBEN <Unicum> ...
 * - SCHREIBEN <composition> <Unicum> ...
 */
final class Write extends UnitCommand implements Activity, Operator
{
	use DefaultActivityTrait;
	use UnicumTrait;

	#[Pure] public function Activity(): string {
		return Operator::ACTIVITY;
	}

	protected function run(): void {
		$id = $this->parseUnicum();
		if (!$this->unicum) {
			$this->message(WriteNoUnicumMessage::class)->p($id);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(WriteNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::WRITE)) {
			$this->getOperate(Practice::WRITE)->write();
		} else {
			$this->message(WriteUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}
}
