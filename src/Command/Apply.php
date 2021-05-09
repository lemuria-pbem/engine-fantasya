<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ApplyOnlyMessage;
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
	protected function initialize(): void {
		parent::initialize();
		//TODO: Check for existing ApplyEffect.
	}

	protected function run(): void {
		switch ($this->phrase->count()) {
			case 1 :
				$amount = 1;
				$class  = $this->phrase->getParameter();
				break;
			case 2 :
				$amount = (int)$this->phrase->getParameter();
				$class  = $this->phrase->getParameter(2);
				break;
			default :
				throw new UnknownCommandException($this);
		}
		if ($amount <= 0) {
			throw new UnknownCommandException($this);
		}
		$potion = self::createCommodity($class);
		if (!($potion instanceof Potion)) {
			throw new UnknownCommandException($this);
		}

		$inventory = $this->unit->Inventory();
		$available = $inventory[$class]->Count();
		if ($available < $amount) {
			if ($available <= 0) {
				$this->message(ApplyNoneMessage::class)->s($potion);
				return;
			}
			$quantity = new Quantity($potion, $available);
			$this->message(ApplyOnlyMessage::class)->i($quantity);
		} else {
			$quantity = new Quantity($potion, $amount);
			$this->message(ApplyMessage::class)->i($quantity);
		}

		$inventory->remove($quantity);

		//TODO: Implement potion effect.
	}
}
