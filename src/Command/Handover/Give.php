<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Handover;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\GiveMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveNoInventoryMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\GiveRejectedMessage;
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

		$amount = (int)$count; // GIB <Unit> <amount> <resource>
		if ((string)$amount !== $count) {
			if (strpos('alles', strtolower($count)) !== 0) { // GIB <Unit> Alles
				$commodity = $count; // GIB <Unit> <commodity> (all of commodity)
			}
			$amount = PHP_INT_MAX;
		}
		if (!$commodity && $amount < PHP_INT_MAX) { // GIB <Unit> Alles <commodity>
			throw new InvalidCommandException($this, 'No resource parameter.');
		}

		if (!$this->checkPermission()) {
			$this->message(GiveFailedMessage::class)->e($this->recipient, GiveFailedMessage::RECIPIENT);
			$gift = $commodity ? new Quantity($this->context->Factory()->commodity($commodity), $amount) : 'all its property';
			$this->message(GiveRejectedMessage::class)->e($this->recipient)->e($this->unit, GiveRejectedMessage::RECIPIENT)->i($gift);
			return;
		}

		if ($commodity) {
			$this->give($commodity, $amount);
		} else {
			$this->giveEverything();
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
	 * Give a single resource.
	 *
	 * @param string $resource
	 * @param int $amount
	 */
	private function give(string $resource, int $amount = PHP_INT_MAX): void {
		$commodity = $this->context->Factory()->commodity($resource);
		$quantity  = $this->unit->Inventory()->offsetGet($commodity);
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
		$this->message(GiveMessage::class)->e($this->recipient, GiveMessage::RECIPIENT)->i($gift);
	}
}
