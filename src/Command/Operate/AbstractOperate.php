<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Party\ReadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowReceivedMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
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
		if ($this->operator->Unicum()->Composition()->supports(Practice::GIVE)) {
			$this->transferTo($recipient);
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::GIVE);
	}

	public function read(): void {
		if (!$this->operator->Unicum()->Composition()->supports(Practice::READ)) {
			throw new UnsupportedOperateException($this->operator->Unicum(), Practice::READ);
		}
		$this->addReadEffect();
	}

	public function write(string $text): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::WRITE);
	}

	protected function transferTo(Unit $recipient): void {
		$unicum = $this->operator->Unicum();
		$unit   = $this->operator->Unit();
		$unit->Treasury()->remove($unicum);
		$recipient->Treasury()->add($unicum);
		$this->message(BestowMessage::class, $unit)->s($unicum->Composition())->e($recipient)->e($unicum, BestowMessage::UNICUM);
		$this->message(BestowReceivedMessage::class, $recipient)->s($unicum->Composition())->e($unit)->e($unicum, BestowMessage::UNICUM);
	}

	protected function addReadEffect(): void {
		$unit     = $this->operator->Unit();
		$effect   = new UnicumRead(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setParty($unit->Party()));
		if ($existing) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}

		$treasury = $effect->Treasury();
		$unicum   = $this->operator->Unicum();
		if (!$treasury->has($unicum->Id())) {
			$treasury->add($unicum);
		}
		$this->message(ReadMessage::class, $unit->Party())->e($unit)->s($unicum->Composition())->e($unicum, ReadMessage::UNICUM);
	}
}
