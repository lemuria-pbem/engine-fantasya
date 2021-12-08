<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Message\Unit\GiveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveNoInventoryMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveNoPersonsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GivePersonsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GivePersonsNoSpaceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GivePersonsOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GivePersonsReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GivePersonsToOwnMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveReceivedFromForeignMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GiveRejectedMessage;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
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
	use CamouflageTrait;
	use GiftTrait;

	protected function run(): void {
		$i               = 1;
		$this->recipient = $this->nextId($i);
		$count           = $this->phrase->getParameter($i++);
		$commodity       = $this->phrase->getLine($i);
		if (!$this->recipient) {
			throw new InvalidCommandException($this, 'No recipient parameter.');
		}
		if ($this->recipient->Region() !== $this->unit->Region()) {
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
			return;
		}

		$this->parseObject($count, $commodity);
		if ($this->commodity instanceof Peasant) {
			$this->givePersons($this->amount === PHP_INT_MAX ? $this->unit->Size() : $this->amount);
			return;
		}

		$isVisible = $this->checkVisibility($this->unit, $this->recipient);
		if (!$this->checkPermission()) {
			if ($isVisible) {
				$this->message(GiveFailedMessage::class)->e($this->recipient);
				$gift = new Quantity($this->commodity, $this->amount);
				$this->message(GiveRejectedMessage::class, $this->recipient)->e($this->unit)->i($gift);
				return;
			}
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
		}

		if (!$isVisible) {
			$this->message(GiveNotFoundMessage::class)->e($this->recipient);
			return;
		}

		if ($this->commodity instanceof Everything) {
			$this->giveEverything();
			if ($this->phrase->count() === 1) {
				$this->givePersons($this->unit->Size());
			}
		} else {
			$this->give($this->commodity, $this->amount);
		}
	}

	#[Pure] protected function checkSize(): bool {
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
		if ($this->recipient->Party() === $this->unit->Party()) {
			$this->message(GiveReceivedMessage::class, $this->recipient)->e($this->unit)->i($gift);
		} else {
			$this->message(GiveReceivedFromForeignMessage::class, $this->recipient)->e($this->unit)->i($gift);
		}
	}

	/**
	 * Transfer persons to own unit.
	 */
	private function givePersons(int $count): void {
		if ($this->recipient->Party() !== $this->unit->Party()) {
			$this->message(GivePersonsToOwnMessage::class);
			return;
		}
		$fromSize = $this->unit->Size();
		if ($count <= 0 || $fromSize <= 0) {
			$this->message(GiveNoPersonsMessage::class);
			return;
		}
		$amount = min($count, $fromSize);
		if ($this->recipient->Construction() && !$this->unit->Construction()) {
			$construction = $this->recipient->Construction();
			$used         = $construction->Inhabitants()->count();
			$free         = $construction->Size() - $used;
			if ($free <= 0) {
				$this->message(GivePersonsNoSpaceMessage::class)->e($this->recipient);
				return;
			}
			$amount = min($amount, $free);
		}

		$toSize = $this->recipient->Size();
		$this->unit->setSize($fromSize - $amount);
		$this->recipient->setSize($toSize + $amount);
		$this->mergeKnowledge($toSize, $amount);
		if ($amount < $count) {
			$this->message(GivePersonsOnlyMessage::class)->e($this->recipient)->p($amount);
		} else {
			$this->message(GivePersonsMessage::class)->e($this->recipient)->p($amount);
		}
		$this->message(GivePersonsReceivedMessage::class, $this->recipient)->e($this->unit)->p($amount);
	}

	private function mergeKnowledge(int $old, int $new): void {
		$talents = [];
		foreach ($this->recipient->Knowledge() as $class => $ability /* @var Ability $ability */) {
			$talents[$class] = $old * $ability->Experience();
		}
		foreach ($this->unit->Knowledge() as $class => $ability /* @var Ability $ability */) {
			if (isset($talents[$class])) {
				$talents[$class] += $new * $ability->Experience();
			} else {
				$talents[$class] = $new * $ability->Experience();
			}
		}

		$size      = $this->recipient->Size();
		$knowledge = $this->recipient->Knowledge();
		$knowledge->clear();
		foreach ($talents as $class => $points) {
			$average = (int)floor($points / $size);
			$knowledge->add(new Ability(self::createTalent($class), $average));
		}
	}
}
