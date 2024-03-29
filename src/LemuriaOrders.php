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

	private const string CURRENT = 'current';

	private const string DEFAULT = 'default';

	private const string ID = 'id';

	private const string ORDERS = 'orders';

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
			$this->loadData($orders);
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
		$data = [self::CURRENT => $current, self::DEFAULT => $default];
		return $this->saveData($data);
	}

	public function clear(): static {
		$this->current = [];
		$this->default = [];
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$old = $oldId->Id();
			$new = $identifiable->Id()->Id();
			if (isset($this->current[$old])) {
				$this->current[$new] = $this->current[$old];
				unset($this->current[$old]);
			}
			if (isset($this->default[$old])) {
				$this->default[$new] = $this->default[$old];
				unset($this->default[$old]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->current[$id]);
			unset($this->default[$id]);
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

	protected function loadData(array $orders): static {
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
		return $this;
	}

	/**
	 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
	 */
	protected function saveData(array &$data): static {
		Lemuria::Game()->setOrders($data);
		return $this;
	}
}
