<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operate\AbstractOperate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePracticeMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unicum;

trait UnicumTrait
{
	protected ?Unicum $unicum;

	protected ?Composition $composition;

	private readonly int $argumentIndex;

	public function ArgumentIndex(): int {
		return $this->argumentIndex;
	}

	public function Unicum(): Unicum {
		return $this->unicum;
	}

	private function parseUnicum(): string {
		$n = $this->phrase->count();
		if ($n === 1) {
			$id                  = $this->phrase->getParameter();
			$this->unicum        = $this->getUnicum($id);
			$this->composition   = $this->unicum?->Composition();
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->getUnicum($id);
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	private function findUnicum(): string {
		$n = $this->phrase->count();
		if ($n === 1) {
			$id                  = $this->phrase->getParameter();
			$this->unicum        = $this->searchUnicum($id);
			$this->composition   = $this->unicum?->Composition();
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->searchUnicum($id);
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	private function parseUnicumWithArguments(): void {
		$id     = $this->phrase->getParameter();
		$unicum = $this->getUnicum($id);
		if ($unicum) {
			$this->unicum        = $unicum;
			$this->composition   = $unicum->Composition();
			$this->argumentIndex = 2;
		} else {
			$id     = $this->phrase->getParameter(2);
			$unicum = $this->getUnicum($id);
			if ($unicum) {
				$this->unicum        = $unicum;
				$this->composition   = $unicum->Composition();
				$this->argumentIndex = 3;
			} else {
				throw new InvalidCommandException($this);
			}
		}
	}

	private function findUnicumWithArguments(): string {
		$id     = $this->phrase->getParameter();
		$unicum = $this->searchUnicum($id);
		if ($unicum) {
			$this->unicum        = $unicum;
			$this->composition   = $unicum->Composition();
			$this->argumentIndex = 2;
		} elseif ($this->phrase->count() >= 2) {
			$id     = $this->phrase->getParameter(2);
			$unicum = $this->searchUnicum($id);
			if ($unicum) {
				$this->unicum        = $unicum;
				$this->composition   = $unicum->Composition();
				$this->argumentIndex = 3;
			} else {
				throw new InvalidCommandException($this);
			}
		} else {
			$this->unicum = null;
		}
		return $id;
	}

	private function getUnicum(string $id): ?Unicum {
		$id       = Id::fromId($id);
		$treasury = $this->unit->Treasury();
		if ($treasury->has($id)) {
			return $treasury[$id];
		}
		return null;
	}

	private function searchUnicum(string $id): ?Unicum {
		$id       = Id::fromId($id);
		$treasury = $this->unit->Construction()?->Treasury();
		if ($treasury && $treasury->has($id)) {
			return $treasury[$id];
		}
		$treasury = $this->unit->Vessel()?->Treasury();
		if ($treasury && $treasury->has($id)) {
			return $treasury[$id];
		}
		$treasury = $this->unit->Region()->Treasury();
		if ($treasury->has($id)) {
			return $treasury[$id];
		}
		return null;
	}

	private function getOperate(Practice $practice): AbstractOperate {
		$operate     = $this->context->Factory()->operateUnicum($this->unicum, $this);
		$id          = (string)$this->unicum->Id();
		$composition = $this->unicum->Composition();
		$this->message(OperatePracticeMessage::class)->p($id)->s($composition)->p($practice->name, OperatePracticeMessage::PRACTICE);
		return $operate;
	}
}
