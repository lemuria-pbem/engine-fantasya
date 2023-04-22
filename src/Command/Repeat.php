<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatNotMessage;

/**
 * Set or unset the repeat option a market trade.
 *
 * - WIEDERHOLEN <trade>
 * - WIEDERHOLEN <trade> Nicht
 * - WIEDERHOLEN [Alle|Alles]
 * - WIEDERHOLEN Nicht|Nichts
 */
final class Repeat extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1) {
			$this->repeatAll();
			return;
		}
		if ($n === 1) {
			$parameter = strtolower($this->phrase->getParameter());
			if (in_array($parameter, ['alle', 'alles'])) {
				$this->repeatAll();
				return;
			}
			if (in_array($parameter, ['nicht', 'nichts'])) {
				$this->repeatNone();
				return;
			}
		}
		if ($n > 2) {
			throw new InvalidCommandException($this);
		}


		$id     = $this->parseId();
		$trades = $this->unit->Trades();
		if (!$trades->has($id)) {
			$this->message(RepeatNoneMessage::class)->p((string)$id);
			return;
		}

		$isRepeat = true;
		if ($n === 2) {
			$parameter = strtolower($this->phrase->getParameter(2));
			if ($parameter === 'nicht') {
				$isRepeat = false;
			} else {
				throw new InvalidCommandException($this);
			}
		}

		$trade = $trades[$id];
		$trade->setIsRepeat($isRepeat);
		if ($isRepeat) {
			$this->message(RepeatMessage::class)->e($trade);
		} else {
			$this->message(RepeatNotMessage::class)->e($trade);
		}
	}

	private function repeatAll(): void {
		foreach ($this->unit->Trades() as $trade) {
			if (!$trade->IsRepeat()) {
				$trade->setIsRepeat(true);
				$this->message(RepeatMessage::class)->e($trade);
			}
		}
	}

	private function repeatNone(): void {
		foreach ($this->unit->Trades() as $trade) {
			if ($trade->IsRepeat()) {
				$trade->setIsRepeat(false);
				$this->message(RepeatNotMessage::class)->e($trade);
			}
		}
	}
}
