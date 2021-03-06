<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Message\Unit\DismissEverybodyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissPeasantsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DismissOnlyPeasantsMessage;
use Lemuria\Item;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Heirs;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

trait GiftTrait
{
	use UnitTrait;

	private int $amount;

	private Commodity $commodity;

	private ?Unit $recipient;

	private function parseObject(string $count, string $commodity): void {
		if ($count === '' || strtolower($count) === 'alles') {
			$this->amount = PHP_INT_MAX; // <COMMAND> Alles [<commodity>]
		} else {
			$this->amount = (int)$count; // <COMMAND> <amount> <commodity>
			if ((string)$this->amount === $count) {
				if ($count <= 0) {
					throw new InvalidCommandException($this, 'Amount must be greater than zero.');
				}
				if (!$commodity) {
					throw new InvalidCommandException($this, 'No commodity parameter.');
				}
			} else {
				$this->amount = PHP_INT_MAX;
				$commodity    = $count; // <COMMAND> <commodity>
			}
		}
		$this->commodity = match (strtolower($commodity)) {
			'' => new Everything(),
			'person', 'personen' => $this->context->Factory()->commodity(Peasant::class),
			default => $this->context->Factory()->commodity($commodity)
		};
	}

	/**
	 * Check recipients acceptance for foreign parties.
	 *
	 * @return bool
	 */
	private function checkPermission(): bool {
		$recipientParty = $this->recipient?->Party();
		if ($recipientParty !== $this->unit->Party()) {
			if (!$recipientParty?->Diplomacy()->has(Relation::GIVE, $this->unit)) {
				return false;
			}
		}
		return true;
	}

	private function dismissPeasants(): void {
		$size = $this->unit->Size();
		if ($size > 0) {
			if ($this->amount < PHP_INT_MAX) {
				if ($size < $this->amount) {
					$this->peasantsToRegion($size);
					$this->unit->setSize(0);
					$this->message(DismissOnlyPeasantsMessage::class)->e($this->unit->Region())->p($size);
				} else {
					$this->peasantsToRegion($this->amount);
					$remaining = $size - $this->amount;
					$this->unit->setSize($remaining);
					if ($remaining > 0) {
						$this->message(DismissPeasantsMessage::class)->e($this->unit->Region())->p($this->amount);
					} else {
						$this->message(DismissEverybodyMessage::class)->e($this->unit->Region());
					}
				}
			} else {
				$this->peasantsToRegion($size);
				$this->unit->setSize(0);
				$this->message(DismissEverybodyMessage::class)->e($this->unit->Region());
			}
		} else {
			$this->message(DismissEmptyMessage::class);
		}
	}

	private function peasantsToRegion(int $peasants): void {
		$commodity = $this->context->Factory()->commodity(Peasant::class);
		$quantity = new Quantity($commodity, $peasants);
		$this->giftToRegion($quantity);
	}

	private function giftToRegion(Item $quantity): void {
		if ($quantity instanceof Quantity) {
			$this->unit->Region()->Resources()->add($quantity);
		}
	}

	private function giftToRandomUnit(Item $quantity): ?Unit {
		if ($quantity instanceof Quantity) {
			$heirs = $this->context->getIntelligence($this->unit->Region())->getHeirs($this->unit, false);
			return $this->giftToRandom($heirs, $quantity);
		}
		return null;
	}

	private function giftToRandom(Heirs $heirs, Quantity $quantity): ?Unit {
		$unit = $heirs->random();
		if ($unit) {
			$unit->Inventory()->add($quantity);
		}
		return $unit;
	}
}
