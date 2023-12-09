<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Treasury;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Validate;

final class UnicumRead extends AbstractPartyEffect
{
	private const TREASURY = 'treasury';

	private const INVENTORY = 'inventory';

	private Treasury $treasury;

	/**
	 * @var array<int, Resources>
	 */
	private array $inventory = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->treasury = new Treasury();
	}

	public function Treasury(): Treasury {
		return $this->treasury;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::TREASURY] = $this->treasury->serialize();
		$inventory = [];
		foreach ($this->inventory as $id => $items) {
			$inventory[$id] = $items->serialize();
		}
		$data[self::INVENTORY] = $inventory;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->treasury->unserialize($data[self::TREASURY]);
		foreach ($data[self::INVENTORY] as $id => $inventory) {
			$resources            = new Resources();
			$this->inventory[$id] = $resources->unserialize($inventory);
		}
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		parent::reassign($oldId, $identifiable);
		if ($this->treasury->has($oldId)) {
			$this->treasury->replace($oldId, $identifiable->Id());
		}
	}

	public function getInventory(Unicum $unicum): Resources {
		return $this->inventory[$unicum->Id()->Id()] ?? new Resources();
	}

	public function setInventory(Unicum $unicum, Resources $inventory): static {
		$this->inventory[$unicum->Id()->Id()] = $inventory;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::TREASURY, Validate::Array);
		$this->validate($data, self::INVENTORY, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
