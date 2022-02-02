<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Use;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePracticeMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unicum;

/**
 * Use an unicum.
 *
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class Operate extends UnitCommand
{
	protected readonly Unicum $unicum;

	public function Unicum(): Unicum {
		return $this->unicum;
	}

	protected function run(): void {
		$unicum   = null;
		$treasure = $this->unit->Treasure();
		if ($this->phrase->count() === 1) {
			$id = Id::fromId($this->phrase->getParameter());
			if ($treasure->has($id)) {
				/** @var Unicum $unicum */
				$unicum = $treasure[$id];
			}
		} else {
			$composition = $this->context->Factory()->composition($this->phrase->getParameter());
			$id          = Id::fromId($this->phrase->getParameter(2));
			if ($treasure->has($id)) {
				/** @var Unicum $unicum */
				$unicum = $this->unit->Treasure()[$id];
				if ($composition !== $unicum->Composition()) {
					$this->message(OperateNoCompositionMessage::class)->s($composition)->p((string)$id);
					return;
				}
			}
		}

		if ($unicum) {
			$operate     = $this->context->Factory()->operateUnicum($unicum, $this);
			$composition = $unicum->Composition();
			$this->message(OperatePracticeMessage::class)->p((string)$id)->s($composition)->p(Practice::APPLY, OperatePracticeMessage::PRACTICE);
			$operate->apply();
		} else {
			$this->message(OperateNoUnicumMessage::class)->p((string)$id);
		}
	}
}
