<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

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

	/**
	 * @var Id|null
	 */
	private ?Id $id = null;

	/**
	 * @var MessageType
	 */
	private MessageType $type;

	/**
	 * @var array|null
	 */
	private ?array $entities = null;

	/**
	 * @var array|null
	 */
	private ?array $singletons = null;

	/**
	 * @var array|null
	 */
	private ?array $items = null;

	/**
	 * @var array|null
	 */
	private ?array $parameters = null;

	/**
	 * Get the ID.
	 *
	 * @return Id
	 */
	public function Id(): Id {
		return $this->id;
	}

	/**
	 * Get the report namespace.
	 *
	 * @return int
	 */
	public function Report(): int {
		return $this->type->Report();
	}

	/**
	 * @return string
	 */
	public function Level(): string {
		return $this->type->Level();
	}

	/**
	 * @return Id
	 */
	public function Entity(): Id {
		return $this->get();
	}

	/**
	 * Set the ID.
	 *
	 * @param Id $id
	 * @return Message
	 */
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
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->type->render($this);
	}

	/**
	 * Get a plain data array of the model's data.
	 *
	 * @return array
	 */
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
	 *
	 * @param array $data
	 * @return Serializable
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

	/**
	 * @param MessageType $type
	 * @return LemuriaMessage
	 */
	public function setType(MessageType $type): LemuriaMessage {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string|null $name
	 * @return Id
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

	/**
	 * @param string|null $name
	 * @return Quantity
	 */
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

	/**
	 * @param string|null $name
	 * @return Singleton
	 */
	public function getSingleton(?string $name = null): Singleton {
		if (!$name) {
			$name = self::SINGLETON;
		}
		if (!isset($this->singletons[$name])) {
			throw new SingletonNotSetException($this, $name);
		}
		return Lemuria::Builder()->create($this->singletons[$name]);
	}

	/**
	 * @param string|null $name
	 * @return mixed
	 */
	public function getParameter(?string $name = null) {
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
	 *
	 * @param Entity $entity
	 * @param string|null $name
	 * @return LemuriaMessage
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
	 *
	 * @param Item $item
	 * @param string|null $name
	 * @return LemuriaMessage
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
	 *
	 * @param Singleton $singleton
	 * @param string|null $name
	 * @return LemuriaMessage
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
	 *
	 * @param bool|float|int|string $value
	 * @param string|null $name
	 * @return LemuriaMessage
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
	 * @param array (string=>mixed) &$data
	 */
	protected function validateSerializedData(&$data): void {
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'type', 'string');
		$this->validateIfExists($data, 'entities', 'array');
		$this->validateIfExists($data, 'singletons', 'array');
		$this->validateIfExists($data, 'items', 'array');
		$this->validateIfExists($data, 'parameters', 'array');
	}
}
