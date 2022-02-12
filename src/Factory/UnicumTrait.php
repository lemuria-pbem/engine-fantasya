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
			$this->argumentIndex = 2;
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->getUnicum($id);
			$this->argumentIndex = 3;
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	/**
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	private function getUnicum(string $id): ?Unicum {
		$id       = Id::fromId($id);
		$treasury = $this->unit->Treasury();
		if ($treasury->has($id)) {
			/** @var Unicum $unicum */
			$unicum = $treasury[$id];
			return $unicum;
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
