<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractOperate
{
	use MessageTrait;

	public function __construct(protected Operator $operator) {
		//TODO Unicum
	}

	public function apply(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::APPLY);
	}

	public function give(Unit $recipient): void {
		$this->transferTo($recipient);
	}

	public function read(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::READ);
	}

	public function write(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::WRITE);
	}

	protected function transferTo(Unit $recipient): void {
		$unicum = $this->operator->Unicum();
		$this->operator->Unit()->Treasure()->remove($unicum);
		$recipient->Treasure()->add($unicum);
		//TODO
	}
}
