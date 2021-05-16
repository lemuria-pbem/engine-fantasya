<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplySaveMessage;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Use a potion.
 *
 * - BENUTZEN <potion>
 * - BENUTZEN <amount> <potion>
 */
final class Apply extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new UnknownCommandException($this);
		}

		$first = $this->phrase->getParameter();
		if (isInt($first)) {
			if ($n < 2) {
				throw new UnknownCommandException($this);
			}
			$amount = (int)$first;
			$class  = $this->phrase->getLine(2);
		} else {
			$amount = 1;
			$class  = $this->phrase->getLine();
		}
		if ($amount <= 0) {
			throw new UnknownCommandException($this);
		}
		$potion = self::createCommodity($class);
		if (!($potion instanceof Potion)) {
			throw new UnknownCommandException($this);
		}

		$apply     = $this->context->Factory()->applyPotion($potion);
		$inventory = $this->unit->Inventory();
		$available = $inventory[$class]->Count();
		if ($available < $amount) {
			if ($available <= 0) {
				$this->message(ApplyNoneMessage::class)->s($potion);
				return;
			}
			if (!$apply->CanApply()) {
				$this->message(ApplyAlreadyMessage::class);
				return;
			}
			$quantity = new Quantity($potion, $available);
			$this->message(ApplyOnlyMessage::class)->i($quantity);
		} else {
			if (!$apply->CanApply()) {
				$this->message(ApplyAlreadyMessage::class);
				return;
			}
			$quantity = new Quantity($potion, $amount);
			$this->message(ApplyMessage::class)->i($quantity);
		}

		$count   = $quantity->Count();
		$applied = $apply->apply($count);
		if ($applied < $count) {
			if ($applied > 0) {
				$used = new Quantity($potion, $applied);
				$inventory->remove($used);
				$saved = new Quantity($potion, $count - $applied);
				$this->message(ApplySaveMessage::class)->i($saved);
			} else {
				$this->message(ApplySaveMessage::class)->i($quantity);
			}
		} else {
			$inventory->remove($quantity);
		}
	}
}
