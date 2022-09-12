<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RepeatNotMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Set or unset the repeat option a market trade.
 *
 * - WIEDERHOLEN <trade>
 * - WIEDERHOLEN <trade> Nicht
 */
final class Repeat extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}

		$id     = Id::fromId($this->phrase->getParameter());
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

		/** @var Trade $trade */
		$trade = $trades[$id];
		$trade->setIsRepeat($isRepeat);
		if ($isRepeat) {
			$this->message(RepeatMessage::class)->e($trade);
		} else {
			$this->message(RepeatNotMessage::class)->e($trade);
		}
	}
}
