<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operate\AbstractOperate;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperateNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePossessionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePracticeMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Ownable;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unicum;

trait OperateTrait
{
	use UnicumTrait;

	public function ArgumentIndex(): int {
		return $this->argumentIndex;
	}

	public function Unicum(): Unicum {
		return $this->unicum;
	}

	protected function parseComposition(): Composition {
		$treasury = $this->unit->Treasury();
		if ($this->phrase->count() === 1) {
			$id = Id::fromId($this->phrase->getParameter());
			if ($treasury->has($id)) {
				$unicum = $treasury[$id];
				return $unicum->Composition();
			}
			$unicum = $this->searchUnicum($id);
			if ($unicum) {
				return $unicum->Composition();
			}
		} else {
			$composition = $this->context->Factory()->composition($this->phrase->getParameter());
			$id          = Id::fromId($this->phrase->getParameter(2));
			if ($treasury->has($id)) {
				$unicum = $treasury[$id];
				if ($composition === $unicum->Composition()) {
					return $composition;
				}
			}
			$unicum = $this->searchUnicum($id);
			if ($composition === $unicum?->Composition()) {
				return $unicum->Composition();
			}
		}
		throw new InvalidCommandException($this, 'Composition could not be determined.');
	}

	protected function parseBestow(): ?AbstractOperate {
		return $this->parseAbstractOperate(Practice::Give, 1);
	}

	protected function parseOperate(Practice $practice): ?AbstractOperate {
		return $this->parseAbstractOperate($practice);
	}

	private function parseAbstractOperate(Practice $practice, int $offset = 0): ?AbstractOperate {
		$treasury = $this->unit->Treasury();
		if ($this->phrase->count() === 1 + $offset) {
			$id = Id::fromId($this->phrase->getParameter(1 + $offset));
			if ($treasury->has($id)) {
				$unicum = $treasury[$id];
			} else {
				$unicum = $this->searchUnicum($id);
				if ($unicum) {
					$composition = $unicum->Composition();
					if ($composition instanceof Ownable) {
						$this->message(OperatePossessionMessage::class)->s($composition)->p((string)$id);
						return null;
					}
				}
			}
			$this->argumentIndex = $offset + 2;
		} else {
			$composition = $this->context->Factory()->composition($this->phrase->getParameter(1 + $offset));
			$id          = Id::fromId($this->phrase->getParameter(2 + $offset));
			if ($treasury->has($id)) {
				$unicum = $treasury[$id];
			} else {
				$unicum = $this->searchUnicum($id);
				if ($unicum?->Composition() instanceof Ownable) {
					$this->message(OperatePossessionMessage::class)->s($composition)->p((string)$id);
					return null;
				}
			}
			if ($unicum && $composition !== $unicum->Composition()) {
				$this->message(OperateNoCompositionMessage::class)->s($composition)->p((string)$id);
				return null;
			}
			$this->argumentIndex = $offset + 3;
		}

		if ($unicum) {
			$this->unicum = $unicum;
			return $this->createOperate($unicum, $practice, $this);
		}
		$this->message(OperateNoUnicumMessage::class)->p((string)$id);
		return null;
	}

	protected function createOperate(Unicum $unicum, Practice $practice, Operator $operator): AbstractOperate {
		$operate     = $this->context->Factory()->operateUnicum($unicum, $operator);
		$composition = $unicum->Composition();
		$this->message(OperatePracticeMessage::class)->p((string)$unicum->Id())->s($composition)->p($practice->name, OperatePracticeMessage::PRACTICE);
		return $operate;
	}
}
