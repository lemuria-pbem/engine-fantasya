<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Effect\UnicumActive;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\RingOfInvisibilityApplyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\RingOfInvisibilityTakeOffMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class RingOfInvisibility extends AbstractOperate
{
	public function apply(): void {
		$unit        = $this->operator->Unit();
		$unicum      = $this->operator->Unicum();
		$argument    = $this->operator->Phrase()->getParameter($this->operator->ArgumentIndex());
		$activate    = strtolower($argument) !== 'nicht';
		$composition = $unicum->Composition();
		$effect      = new UnicumActive(State::getInstance());
		$existing    = Lemuria::Score()->find($effect->setUnicum($unicum));
		if ($activate) {
			if (!$existing) {
				Lemuria::Score()->add($effect);
			}
			$this->message(RingOfInvisibilityApplyMessage::class, $unit)->s($composition)->e($unicum);
		} else {
			if ($existing) {
				Lemuria::Score()->remove($existing);
			}
			$this->message(RingOfInvisibilityTakeOffMessage::class, $unit)->s($composition)->e($unicum);
		}
	}
}
