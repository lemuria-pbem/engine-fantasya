<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\BestowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowReceivedMessage;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractOperate
{
	use MessageTrait;

	public function __construct(protected Operator $operator) {
	}

	public function apply(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::APPLY);
	}

	public function give(Unit $recipient): void {
		$this->transferTo($recipient);
	}

	public function read(): void {

	}

	public function write(string $text): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::WRITE);
	}

	protected function transferTo(Unit $recipient): void {
		$unicum = $this->operator->Unicum();
		$unit   = $this->operator->Unit();
		$unit->Treasure()->remove($unicum);
		$recipient->Treasure()->add($unicum);
		$this->message(BestowMessage::class, $unit)->s($unicum->Composition())->e($recipient)->e($unicum, BestowMessage::UNICUM);
		$this->message(BestowReceivedMessage::class, $recipient)->s($unicum->Composition())->e($unit)->e($unicum, BestowMessage::UNICUM);
	}
}
