<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Everything;
use Lemuria\Engine\Lemuria\Message\Unit\DismissEverybodyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissEmptyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissPeasantsMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DismissOnlyPeasantsMessage;
use Lemuria\Item;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Unit;

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
			$intelligence = $this->context->getIntelligence($this->unit->Region());
			$parties      = $intelligence->getParties();
			$parties->remove($this->unit->Party());
			if ($parties->count() > 0) {
				/** @var Party $party */
				$party = $parties->random();
				$units = $party->People();
				if ($units->count() > 0) {
					/** @var Unit $unit */
					$unit = $units->random();
					$unit->Inventory()->add($quantity);
					return $unit;
				}
			}
		}
		return null;
	}
}
