<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Message\Unit\GiveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveNoInventoryMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveRejectedMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Implementation of command GIB.
 *
 * The command transfers commodities to another Unit.
 *
 * - GIB <Unit>
 * - GIB <Unit> Alles
 * - GIB <Unit> <commodity>
 * - GIB <Unit> Alles <commodity>
 * - GIB <Unit> <amount> <commodity>
 */
final class Give extends UnitCommand
{
	use GiftTrait;

	protected function run(): void {
		$i               = 1;
		$this->recipient = $this->nextId($i);
		$count           = $this->phrase->getParameter($i++);
		$commodity       = $this->phrase->getParameter($i);
		if (!$this->recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}

		$this->parseObject($count, $commodity);
		if (!$this->checkPermission()) {
			$this->message(GiveFailedMessage::class)->e($this->recipient);
			$gift = new Quantity($this->commodity, $this->amount);
			$this->message(GiveRejectedMessage::class, $this->recipient)->e($this->unit)->i($gift);
			return;
		}

		if ($commodity instanceof Everything) {
			$this->giveEverything();
		} else {
			$this->give($this->commodity, $this->amount);
		}
	}

	/**
	 * Give a single commodity.
	 *
	 * @param Commodity $commodity
	 * @param int $amount
	 */
	private function give(Commodity $commodity, int $amount): void {
		$quantity = $this->unit->Inventory()->offsetGet($commodity);
		if ($quantity instanceof Quantity) {
			$gift = new Quantity($commodity, min($quantity->Count(), $amount));
			$this->giveQuantity($gift);
		} else {
			$this->message(GiveNoInventoryMessage::class)->s($commodity);
		}
	}

	/**
	 * Give all resources.
	 */
	private function giveEverything(): void {
		$inventory = $this->unit->Inventory();
		foreach ($inventory as $quantity /* @var Quantity $quantity */) {
			$gift = new Quantity($quantity->Commodity(), $quantity->Count());
			$this->giveOnly($gift);
		}
		$inventory->clear();
	}

	/**
	 * Give quantity.
	 *
	 * @param Quantity $gift
	 */
	private function giveQuantity(Quantity $gift): void {
		$this->unit->Inventory()->remove($gift);
		$this->giveOnly($gift);
	}

	/**
	 * Add quantity to recipient.
	 *
	 * @param Quantity $gift
	 */
	private function giveOnly(Quantity $gift): void {
		$this->recipient->Inventory()->add($gift);
		$this->message(GiveMessage::class)->e($this->recipient)->i($gift);
	}
}
