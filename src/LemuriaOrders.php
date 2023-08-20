<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Instructions;
use Lemuria\Engine\Orders;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\SerializableTrait;
use Lemuria\StringList;
use Lemuria\Validate;

class LemuriaOrders implements Orders
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
			$this->default[$id] = new StringList();
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

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::CURRENT, Validate::Array);
		$this->validate($data, self::DEFAULT, Validate::Array);
	}
}
