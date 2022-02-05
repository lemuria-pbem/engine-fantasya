<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operate\AbstractOperate;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePracticeMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unicum;

trait OperateTrait
{
	protected readonly Unicum $unicum;

	public function Unicum(): Unicum {
		return $this->unicum;
	}

	protected function parseBestow(): ?AbstractOperate {
		return $this->parseAbstractOperate(Practice::GIVE, 1);
	}

	protected function parseOperate(Practice $practice): ?AbstractOperate {
		return $this->parseAbstractOperate($practice);
	}

	private function parseAbstractOperate(Practice $practice, int $offset = 0): ?AbstractOperate {
		$unicum   = null;
		$treasure = $this->unit->Treasure();
		if ($this->phrase->count() === 1 + $offset) {
			$id = Id::fromId($this->phrase->getParameter(1 + $offset));
			if ($treasure->has($id)) {
				/** @var Unicum $unicum */
				$unicum = $treasure[$id];
			}
		} else {
			$composition = $this->context->Factory()->composition($this->phrase->getParameter(1 + $offset));
			$id          = Id::fromId($this->phrase->getParameter(2 + $offset));
			if ($treasure->has($id)) {
				/** @var Unicum $unicum */
				$unicum = $this->unit->Treasure()[$id];
				if ($composition !== $unicum->Composition()) {
					$this->message(OperateNoCompositionMessage::class)->s($composition)->p((string)$id);
					return null;
				}
			}
		}

		if ($unicum) {
			$operate     = $this->context->Factory()->operateUnicum($unicum, $this);
			$composition = $unicum->Composition();
			$this->message(OperatePracticeMessage::class)->p((string)$id)->s($composition)->p($practice, OperatePracticeMessage::PRACTICE);
			return $operate;
		}
		$this->message(OperateNoUnicumMessage::class)->p((string)$id);
		return null;
	}
}
