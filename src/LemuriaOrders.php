<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Instructions;
use Lemuria\Engine\Orders;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Reassignment;
use Lemuria\SerializableTrait;
use Lemuria\StringList;
use Lemuria\Validate;

class LemuriaOrders implements Orders, Reassignment
{
	use SerializableTrait;

	private const CURRENT = 'current';

	private const DEFAULT = 'default';

	private const ID = 'id';

	private const ORDERS = 'orders';

	/**
	 * @var array<int, array>
	 */
	private array $current = [];

	/**
	 * @var array<int, array>
	 */
	private array $default = [];

	private bool $isLoaded = false;

	public function __construct() {
		Lemuria::Catalog()->addReassignment($this);
	}

	/**
	 * Get the list of current orders for an entity.
	 */
	public function getCurrent(Id $id): Instructions {
		$id = $id->Id();
		if (!isset($this->current[$id])) {
			$this->current[$id] = new StringList();
		}
		return $this->current[$id];
	}

	/**
	 * Get the list of new default orders for an entity.
	 */
	public function getDefault(Id $id): Instructions {
		$id = $id->Id();
		if (!isset($this->default[$id])) {
			$this->default[$id] = new LemuriaInstructions();
		}
		return $this->default[$id];
	}

	/**
	 * Load orders data.
	 */
	public function load(): static {
		if (!$this->isLoaded) {
			$orders = Lemuria::Game()->getOrders();
			$this->validateSerializedData($orders);
			foreach ($orders[self::CURRENT] as $data) {
				$this->validate($data, self::ID, Validate::Int);
				$this->validate($data, self::ORDERS, Validate::Array);
				$this->getCurrent(new Id($data[self::ID]))->unserialize($data[self::ORDERS]);
			}
			foreach ($orders[self::DEFAULT] as $data) {
				$this->validate($data, self::ID, Validate::Int);
				$this->validate($data, self::ORDERS, Validate::Array);
				$this->getDefault(new Id($data[self::ID]))->unserialize($data[self::ORDERS]);
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save orders data.
	 */
	public function save(): static {
		$current = [];
		$default = [];
		ksort($this->current);
		foreach ($this->current as $id => $instructions /** @var Instructions $instructions */) {
			$current[] = [self::ID => $id, self::ORDERS => $instructions->serialize()];
		}
		ksort($this->default);
		foreach ($this->default as $id => $instructions /** @var Instructions $instructions */) {
			$default[] = [self::ID => $id, self::ORDERS => $instructions->serialize()];
		}
		Lemuria::Game()->setOrders([self::CURRENT => $current, self::DEFAULT => $default]);
		return $this;
	}

	public function clear(): static {
		$this->current = [];
		$this->default = [];
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$this->replace($oldId->Id(), $identifiable->Id()->Id(), $this->current);
			$this->replace($oldId->Id(), $identifiable->Id()->Id(), $this->default);
			$this->replaceInDefaults($oldId, $identifiable->Id());
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->current[$id]);
			unset($this->default[$id]);
			$this->replaceInDefaults($identifiable->Id());
		}
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::CURRENT, Validate::Array);
		$this->validate($data, self::DEFAULT, Validate::Array);
	}

	private function replace(int $old, int $new, array &$instructions): void {
		if (!isset($instructions[$old])) {
			return;
		}
		$oldOrders = $instructions[$old];
		unset($instructions[$old]);

		if (isset($instructions[$new])) {
			/** @var Instructions $newOrdners */
			$newOrders = $instructions[$new];
			foreach ($oldOrders as $instruction) {
				$newOrders[] = $instruction;
			}
		} else {
			$instructions[$new] = $oldOrders;
		}
	}

	private function replaceInDefaults(Id $old, ?Id $new = null): void {
		$oldId = (string)$old;
		$newId = $new ? (string)$new : null;
		foreach ($this->default as $instructions /** @var LemuriaInstructions $instructions */) {
			$instructions->replace($oldId, $newId);
		}
	}
}
