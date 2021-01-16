<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Handover;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Everything;
use Lemuria\Engine\Lemuria\Message\Unit\GiveMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveNoInventoryMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveRejectedMessage;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Unit;
use Lemuria\Model\Lemuria\Quantity;

/**
 * Implementation of command GIB.
 *
 * The command transfers commodities to another Unit.
 *
 * - GIB <Unit> Alles
 * - GIB <Unit> <commodity>
 * - GIB <Unit> Alles <commodity>
 * - GIB <Unit> <amount> <commodity>
 */
final class Give extends UnitCommand
{
	private ?Unit $recipient;

	protected function run(): void {
		$i               = 1;
		$this->recipient = $this->nextId($i);
		$count           = $this->phrase->getParameter($i++);
		$commodity       = $this->phrase->getParameter($i);
		if (!$this->recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}

		if (strtolower($count) === 'alles') {
			$amount = PHP_INT_MAX; // GIB <Unit> Alles [<commodity>]
		} else {
			$amount = (int)$count; // GIB <Unit> <amount> <commodity>
			if ((string)$amount === $count) {
				if (!$commodity) {
					throw new InvalidCommandException($this, 'No commodity parameter.');
				}
			} else {
				$amount = PHP_INT_MAX;
				$commodity = $count; // GIB <Unit> <commodity>
			}
		}
		$commodity = $commodity ? $this->context->Factory()->commodity($commodity) : new Everything();

		if (!$this->checkPermission()) {
			$this->message(GiveFailedMessage::class)->e($this->recipient);
			$gift = new Quantity($commodity, $amount);
			$this->message(GiveRejectedMessage::class, $this->recipient)->e($this->unit)->i($gift);
			return;
		}

		if ($commodity instanceof Everything) {
			$this->giveEverything();
		} else {
			$this->give($commodity, $amount);
		}
	}

	/**
	 * Check recipients acceptance for foreign parties.
	 *
	 * @return bool
	 */
	private function checkPermission(): bool {
		$recipientParty = $this->recipient->Party();
		if ($recipientParty !== $this->unit->Party()) {
			if (!$recipientParty->Diplomacy()->has(Relation::GIVE, $this->unit)) {
				return false;
			}
		}
		return true;
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
