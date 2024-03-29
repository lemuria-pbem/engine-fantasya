<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\UnicumDisintegrate;
use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\Effect\UnicumRemoval;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Party\ReadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowReceivedForeignMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BestowReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractOperate
{
	use MessageTrait;

	protected Unit $unit;

	public function __construct(protected Context $context, protected Operator $operator) {
		$this->unit = $operator->Unit();
	}

	public function apply(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Apply);
	}

	public function take(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::Take)) {
			$collector = $unicum->Collector();
			if ($collector instanceof Unit) {
				throw new LemuriaException('Unexpected Unit collector in take().');
			}

			$unit = $this->operator->Unit();
			$collector->Treasury()->remove($unicum);
			$unit->Treasury()->add($unicum);
			$this->addReadEffect()->Treasury()->add($unicum);
			$this->message(TakeMessage::class, $unit)->s($composition)->e($unicum);
			return;
		}

		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Take);
	}

	public function give(Unit $recipient): void {
		if ($this->operator->Unicum()->Composition()->supports(Practice::Give)) {
			$this->transferTo($recipient);
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Give);
	}

	public function lose(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::Lose)) {
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
			$this->addLooseEffect();
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Lose);
	}

	public function destroy(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		if ($composition->supports(Practice::Destroy)) {
			Lemuria::Catalog()->reassign($unicum);
			$unicum->Collector()->Treasury()->remove($unicum);
			$this->addRemovalEffect($unicum);
			$this->destroyMessage();
			return;
		}
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Destroy);
	}

	public function read(): UnicumRead {
		$unicum = $this->operator->Unicum();
		if (!$unicum->Composition()->supports(Practice::Read)) {
			throw new UnsupportedOperateException($unicum, Practice::Read);
		}

		$party  = $this->unit->Party();
		$effect = $this->addReadEffect();
		$effect->Treasury()->add($unicum);
		$this->message(ReadMessage::class, $party)->e($this->unit)->s($unicum->Composition())->e($unicum, ReadMessage::UNICUM);
		return $effect;
	}

	public function write(): void {
		throw new UnsupportedOperateException($this->operator->Unicum(), Practice::Write);
	}

	protected function destroyMessage(): void {
	}

	protected function addLooseEffect(): void {
		$unicum = $this->operator->Unicum();
		Lemuria::Log()->debug('Lost ' . $unicum->Composition() . ' ' . $unicum->Id() . ' will not disintegrate.');
	}

	protected function addDisintegrateEffectForRegion(int $rounds): void {
		$unicum    = $this->operator->Unicum();
		$collector = $unicum->Collector();
		if ($collector instanceof Region) {
			$this->addDisintegrateEffect()->setRounds($rounds);
			Lemuria::Log()->debug('Lost ' . $unicum->Composition() . ' ' . $unicum->Id() . ' in ' . $collector . ' will disintegrate in ' . $rounds . ' rounds.');
		}
	}

	protected function transferTo(Unit $recipient): void {
		$unicum = $this->operator->Unicum();
		$unit   = $this->operator->Unit();
		$unit->Treasury()->remove($unicum);
		$recipient->Treasury()->add($unicum);
		$this->message(BestowMessage::class, $unit)->s($unicum->Composition())->e($recipient)->e($unicum, BestowMessage::UNICUM);
		if ($recipient->Party() === $unit->Party()) {
			$this->message(BestowReceivedMessage::class, $recipient)->s($unicum->Composition())->e($unit)->e($unicum, BestowMessage::UNICUM);
		} else {
			$this->message(BestowReceivedForeignMessage::class, $recipient)->s($unicum->Composition())->e($unit)->e($unicum, BestowMessage::UNICUM);
		}
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

	private function addDisintegrateEffect(): UnicumDisintegrate {
		$effect = new UnicumDisintegrate(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnicum($this->operator->Unicum()));
		if ($existing instanceof UnicumDisintegrate) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}

	private function addRemovalEffect(Unicum $unicum): void {
		$effect = new UnicumRemoval(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnicum($unicum))) {
			Lemuria::Score()->add($effect);
		}
	}
}
