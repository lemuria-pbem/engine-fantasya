<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Party\ReadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractOperate
{
	use MessageTrait;

	protected Unit $unit;

	public function __construct(protected Context $context, protected Operator $operator) {
		$this->unit = $operator->Unit();
	}

	public function apply(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::APPLY);
	}

	public function take(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::TAKE)) {
			$collector = $unicum->Collector();
			if ($collector instanceof Unit) {
				throw new LemuriaException('Unexpected Unit collector in take().');
			}

			$collector->Treasury()->remove($unicum);
			$this->operator->Unit()->Treasury()->add($unicum);
			$this->addReadEffect()->Treasury()->add($unicum);
			$this->message(TakeMessage::class)->s($composition)->e($unicum);
			return;
		}

		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::TAKE);
	}

	public function give(Unit $recipient): void {
		if ($this->operator->Unicum()->Composition()->supports(Practice::GIVE)) {
			$this->transferTo($recipient);
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::GIVE);
	}

	public function lose(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::LOSE)) {
			$location = $this->unit->Construction();
			if (!$location) {
				$location = $this->unit->Vessel();
			}
			if (!$location) {
				$location = $this->unit->Region();
			}
			$this->unit->Treasury()->remove($unicum);
			$location->Treasury()->add($unicum);
			$this->message(LoseUnicumMessage::class, $this->unit)->s($composition)->e($unicum);
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::LOSE);
	}

	public function destroy(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::DESTROY)) {
			$this->unit->Treasury()->remove($unicum);
			$this->destroyMessage();
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::DESTROY);
	}

	public function read(): void {
		$unicum = $this->operator->Unicum();
		if (!$unicum->Composition()->supports(Practice::READ)) {
			throw new UnsupportedOperateException($unicum, Practice::READ);
		}

		$party = $this->unit->Party();
		$this->addReadEffect()->Treasury()->add($unicum);
		$this->message(ReadMessage::class, $party)->e($this->unit)->s($unicum->Composition())->e($unicum, ReadMessage::UNICUM);
	}

	public function write(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::WRITE);
	}

	abstract protected function destroyMessage(): void;

	protected function transferTo(Unit $recipient): void {
		$unicum = $this->operator->Unicum();
		$unit   = $this->operator->Unit();
		$unit->Treasury()->remove($unicum);
		$recipient->Treasury()->add($unicum);
		$this->message(BestowMessage::class, $unit)->s($unicum->Composition())->e($recipient)->e($unicum, BestowMessage::UNICUM);
		$this->message(BestowReceivedMessage::class, $recipient)->s($unicum->Composition())->e($unit)->e($unicum, BestowMessage::UNICUM);
	}

	private function addReadEffect(): UnicumRead {
		$effect   = new UnicumRead(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setParty($this->unit->Party()));
		if ($existing instanceof UnicumRead) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
