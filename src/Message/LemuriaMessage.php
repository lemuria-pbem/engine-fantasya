<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Singleton;
use Lemuria\Engine\Lemuria\Factory\BuilderTrait as EngineBuilderTrait;
use Lemuria\Engine\Lemuria\Message\Exception\EntityNotSetException;
use Lemuria\Engine\Lemuria\Message\Exception\ItemNotSetException;
use Lemuria\Engine\Lemuria\Message\Exception\ParameterNotSetException;
use Lemuria\Engine\Lemuria\Message\Exception\SingletonNotSetException;
use Lemuria\Engine\Message;
use Lemuria\Entity;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Item;
use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Factory\BuilderTrait as ModelBuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

class LemuriaMessage implements Message
{
	use EngineBuilderTrait;
	use ModelBuilderTrait;
	use SerializableTrait;

	private const ENTITY = 'e';

	private const SINGLETON = 's';

	private const ITEM = 'i';

	private const PARAMETER = 'p';

	private ?Id $id = null;

	private MessageType $type;

	private ?array $entities = null;

	private ?array $singletons = null;

	private ?array $items = null;

	private ?array $parameters = null;

	#[Pure] public function Id(): Id {
		return $this->id;
	}

	#[Pure] public function Report(): int {
		return $this->type->Report();
	}

	#[ExpectedValues(valuesFromClass: Message::class)]
	#[Pure]
	public function Level(): string {
		return $this->type->Level();
	}

	#[Pure]
	public function Entity(): Id {
		return $this->get();
	}

	public function setId(Id $id): Message {
		if ($this->id) {
			throw new LemuriaException('Cannot set ID twice.');
		}

		$this->id = $id;
		Lemuria::Report()->register($this);
		return $this;
	}

	/**
	 * Get the message text.
	 */
	public function __toString(): string {
		return $this->type->render($this);
	}

	/**
	 * Get a plain data array of the model's data.
	 */
	#[ArrayShape(['id' => 'int', 'type' => 'string', 'parameters' => 'array|null', 'items' => 'array|null', 'singletons' => 'array|null', 'entities' => 'array|null'])]
	#[Pure]
	public function serialize(): array {
		$data = ['id' => $this->Id()->Id(), 'type' => getClass($this->type)];
		if ($this->entities) {
			$data['entities'] = $this->entities;
		}
		if ($this->singletons) {
			$data['singletons'] = $this->singletons;
		}
		if ($this->items) {
			$data['items'] = $this->items;
		}
		if ($this->parameters) {
			$data['parameters'] = $this->parameters;
		}
		return $data;
	}

	/**
	 * Restore the model's data from serialized data.
	 */
	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->setId(new Id($data['id']))->setType(self::createMessageType($data['type']));
		if (isset($data['entities'])) {
			$this->entities = $data['entities'];
		}
		if (isset($data['singletons'])) {
			$this->singletons = $data['singletons'];
		}
		if (isset($data['items'])) {
			$this->items = $data['items'];
		}
		if (isset($data['parameters'])) {
			$this->parameters = $data['parameters'];
		}
		return $this;
	}

	public function setType(MessageType $type): LemuriaMessage {
		$this->type = $type;
		return $this;
	}

	/**
	 * @throws EntityNotSetException
	 */
	public function get(?string $name = null): Id {
		if (!$name) {
			$name = self::ENTITY;
		}
		if (!isset($this->entities[$name])) {
			throw new EntityNotSetException($this, $name);
		}
		return new Id($this->entities[$name]);
	}

	public function getQuantity(?string $name = null): Quantity {
		if (!$name) {
			$name = self::ITEM;
		}
		if (!isset($this->items[$name])) {
			throw new ItemNotSetException($this, $name);
		}
		$item  = $this->items[$name];
		$class = key($item);
		$count = current($item);
		if (!is_string($class) || !is_int($count)) {
			throw new LemuriaException('Item ' . $name . ' is invalid.');
		}
		return new Quantity(self::createCommodity($class), $count);
	}

	public function getSingleton(?string $name = null): Singleton {
		if (!$name) {
			$name = self::SINGLETON;
		}
		if (!isset($this->singletons[$name])) {
			throw new SingletonNotSetException($this, $name);
		}
		return Lemuria::Builder()->create($this->singletons[$name]);
	}

	public function getParameter(?string $name = null): mixed {
		if (!$name) {
			$name = self::PARAMETER;
		}
		if (!isset($this->parameters[$name])) {
			throw new ParameterNotSetException($this, $name);
		}
		return $this->parameters[$name];
	}

	/**
	 * Set an entity.
	 */
	public function e(Entity $entity, ?string $name = null): LemuriaMessage {
		if (!$name) {
			$name = self::ENTITY;
		}
		if (!$this->entities) {
			$this->entities = [];
		}
		$this->entities[$name] = $entity->Id()->Id();
		return $this;
	}

	/**
	 * Set an item.
	 */
	public function i(Item $item, ?string $name = null): LemuriaMessage {
		if (!$name) {
			$name = self::ITEM;
		}
		$this->items[$name] = [getClass($item->getObject()) => $item->Count()];
		return $this;
	}

	/**
	 * Set a Singleton.
	 */
	public function s(Singleton $singleton, ?string $name = null): LemuriaMessage {
		if (!$name) {
			$name = self::SINGLETON;
		}
		if (!$this->singletons) {
			$this->singletons = [];
		}
		$this->singletons[$name] = getClass($singleton);
		return $this;
	}

	/**
	 * Set a parameter.
	 */
	public function p($value, ?string $name = null): LemuriaMessage {
		if (!$name) {
			$name = self::PARAMETER;
		}
		if (!$this->parameters) {
			$this->parameters = [];
		}
		$this->parameters[$name] = $value;
		return $this;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'type', 'string');
		$this->validateIfExists($data, 'entities', 'array');
		$this->validateIfExists($data, 'singletons', 'array');
		$this->validateIfExists($data, 'items', 'array');
		$this->validateIfExists($data, 'parameters', 'array');
	}
}
