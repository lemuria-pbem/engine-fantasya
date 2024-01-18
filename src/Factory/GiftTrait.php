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
use Lemuria\Model\Fantasya\Container;
use Lemuria\Model\Fantasya\Heirs;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

trait GiftTrait
{
	use UnitTrait;

	/**
	 * @type array<string>
	 */
	private const array EVERYTHING = ['alle', 'alles'];

	private int $amount;

	private Commodity $commodity;

	private ?Unit $recipient;

	private function parseObject(string $count, string $commodity): void {
		if ($count === '' || in_array(strtolower($count), self::EVERYTHING)) {
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
				$commodity    = trim($count . ' ' . $commodity); // <COMMAND> <commodity>
			}
		}
		$this->commodity = match (strtolower($commodity)) {
			''                   => new Everything(),
			'person', 'personen' => $this->context->Factory()->person(),
			default              => $this->parseCommodity($commodity)
		};
	}

	private function parseCommodity(string $commodity): Commodity {
		$factory   = $this->context->Factory();
		$container = $factory->kind($commodity);
		return $container ?: $factory->commodity($commodity);
	}

	private function fillResources(int &$resourceCount): Resources {
		$resources = new Resources();
		$inventory = $this->unit->Inventory();
		if ($this->commodity instanceof Everything) {
			$resources->fill($inventory);
			$resourceCount = $this->unit->Size();
		} elseif ($this->commodity instanceof Container) {
			$this->commodity->setResources($inventory);
			foreach ($this->commodity->Commodities() as $commodity /** @var Commodity $commodity */) {
				$quantity = $inventory[$commodity];
				$count    = $this->amount === PHP_INT_MAX ? $quantity->Count() : $this->amount;
				$resources->add(new Quantity($commodity, $count));
			}
		} elseif ($this->commodity instanceof Peasant) {
			$resourceCount = $this->amount === PHP_INT_MAX ? $this->unit->Size() : $this->amount;
		} else {
			$resourceCount = $this->amount === PHP_INT_MAX ? $inventory[$this->commodity]->Count() : $this->amount;
		}
		return $resources;
	}

	/**
	 * Check recipients' acceptance for foreign parties.
	 *
	 * @return bool
	 */
	private function checkPermission(): bool {
		$recipientParty = $this->recipient?->Party();
		if ($recipientParty !== $this->unit->Party()) {
			if ($this->context->getTurnOptions()->IsSimulation() || !$recipientParty?->Diplomacy()->has(Relation::GIVE, $this->unit)) {
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
		$commodity = $this->context->Factory()->person();
		$quantity  = new Quantity($commodity, $peasants);
		$this->giftToRegion($quantity);
	}

	private function giftToRegion(Item $quantity): void {
		if ($quantity instanceof Quantity) {
			$this->unit->Region()->Resources()->add($quantity);
		}
	}

	private function giftToRandomUnit(Item $quantity, Unit $unit = null): ?Unit {
		if (!$unit) {
			$unit = $this->unit;
		}
		if ($quantity instanceof Quantity) {
			$heirs = $this->context->getIntelligence($unit->Region())->getLooters($unit, $quantity->Commodity());
			return $this->giftToRandom($heirs, $quantity);
		}
		return null;
	}

	private function giftToRandom(Heirs $heirs, Quantity $quantity): ?Unit {
		$unit = $heirs->random();
		$unit?->Inventory()->add($quantity);
		return $unit;
	}
}
