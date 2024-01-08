<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use function Lemuria\getClass;
use Lemuria\Singleton;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait as EngineBuilderTrait;
use Lemuria\Engine\Fantasya\Message\Exception\EntityNotSetException;
use Lemuria\Engine\Fantasya\Message\Exception\ItemNotSetException;
use Lemuria\Engine\Fantasya\Message\Exception\ParameterNotSetException;
use Lemuria\Engine\Fantasya\Message\Exception\SingletonNotSetException;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Entity;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Item;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Factory\BuilderTrait as ModelBuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class LemuriaMessage implements Message
{
	use EngineBuilderTrait;
	use ModelBuilderTrait;
	use SerializableTrait;

	private const string ID = 'id';

	private const string TYPE = 'type';

	private const string ASSIGNEE = 'assignee';

	private const string NEW_ASSIGNEE = 'newAssignee';

	private const string ENTITIES = 'entities';

	private const string SINGLETONS = 'singletons';

	private const string ITEMS = 'items';

	private const string PARAMETERS = 'parameters';

	private const string ENTITY = 'e';

	private const string SINGLETON = 's';

	private const string ITEM = 'i';

	private const string PARAMETER = 'p';

	private ?Id $id = null;

	private MessageType $type;

	private Id $assignee;

	private ?Id $newAssignee = null;

	private ?array $entities = null;

	private ?array $singletons = null;

	private ?array $items = null;

	private ?array $parameters = null;

	public function Id(): Id {
		return $this->id;
	}

	public function Result(): Result {
		return $this->type->Result();
	}

	public function Report(): Domain {
		return $this->type->Report();
	}

	public function Assignee(): Id {
		return $this->assignee;
	}

	public function Entity(): Id {
		return $this->newAssignee ?? $this->assignee;
	}

	public function Section(): Section {
		return $this->type->Section();
	}

	public function MessageClass(): string {
		return $this->type::class;
	}

	public function MessageType(): MessageType {
		return $this->type;
	}

	public function setId(Id $id): static {
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
	public function serialize(): array {
		$data = [self::ID => $this->Id()->Id(), self::TYPE => getClass($this->type), self::ASSIGNEE => $this->assignee->Id()];
		if ($this->newAssignee) {
			$data[self::NEW_ASSIGNEE] = $this->newAssignee->Id();
		}
		if ($this->entities) {
			$data[self::ENTITIES] = $this->entities;
		}
		if ($this->singletons) {
			$data[self::SINGLETONS] = $this->singletons;
		}
		if ($this->items) {
			$data[self::ITEMS] = $this->items;
		}
		if ($this->parameters) {
			$data[self::PARAMETERS] = $this->parameters;
		}
		return $data;
	}

	/**
	 * Restore the model's data from serialized data.
	 */
	public function unserialize(array $data): static {
		$this->validateSerializedData($data);
		if (isset($data[self::NEW_ASSIGNEE])) {
			$this->newAssignee = new Id($data[self::NEW_ASSIGNEE]);
		}
		if (isset($data[self::ENTITIES])) {
			$this->entities = $data[self::ENTITIES];
		}
		if (isset($data[self::SINGLETONS])) {
			$this->singletons = $data[self::SINGLETONS];
		}
		if (isset($data[self::ITEMS])) {
			$this->items = $data[self::ITEMS];
		}
		if (isset($data[self::PARAMETERS])) {
			$this->parameters = $data[self::PARAMETERS];
		}
		$message = self::createMessageType($data[self::TYPE]);
		$this->setType($message)->setAssignee(new Id($data[self::ASSIGNEE]))->setId(new Id($data[self::ID]));
		return $this;
	}

	public function setType(MessageType $type): static {
		$this->type = $type;
		return $this;
	}

	public function setAssignee(Entity|Id $assignee): static {
		$this->assignee = $assignee instanceof Entity ? $assignee->Id() : $assignee;
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

	/**
	 * @return array<Entity>
	 */
	public function getEntities(): array {
		$i        = 0;
		$entities = [];
		while (isset($this->entities[self::ENTITY . ++$i])) {
			$entities[] = $this->get(self::ENTITY . $i);
		}
		return $entities;
	}

	public function getGang(?string $name = null): Gang {
		$item = $this->parseItem($name);
		return new Gang(self::createRace(key($item)), current($item));
	}

	public function getQuantity(?string $name = null): Quantity {
		$item = $this->parseItem($name);
		return new Quantity(self::createCommodity(key($item)), current($item));
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

	public function getParameter(?string $name = null): array|bool|float|int|string {
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
	public function e(Identifiable $entity, ?string $name = null): static {
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
	 * Set a number of entities.
	 *
	 * @param array<Entity> $entities
	 * @return LemuriaMessage
	 */
	public function entities(array $entities): static {
		$i = 0;
		foreach ($entities as $entity) {
			$this->e($entity, 'e' . ++$i);
		}
		return $this;
	}

	/**
	 * Set an item.
	 */
	public function i(Item $item, ?string $name = null): static {
		if (!$name) {
			$name = self::ITEM;
		}
		$this->items[$name] = [getClass($item->getObject()) => $item->Count()];
		return $this;
	}

	/**
	 * Set a Singleton.
	 */
	public function s(Singleton $singleton, ?string $name = null): static {
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
	public function p(array|bool|float|int|string $value, ?string $name = null): static {
		if (!$name) {
			$name = self::PARAMETER;
		}
		if (!$this->parameters) {
			$this->parameters = [];
		}
		$this->parameters[$name] = $value;
		return $this;
	}

	public function reassign(Id $newAssignee): void {
		$this->newAssignee = $newAssignee;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::ID, Validate::Int);
		$this->validate($data, self::TYPE, Validate::String);
		$this->validate($data, self::ASSIGNEE, Validate::Int);
		$this->validateIfExists($data, self::NEW_ASSIGNEE, Validate::Int);
		$this->validateIfExists($data, self::ENTITIES, Validate::Array);
		$this->validateIfExists($data, self::SINGLETONS, Validate::Array);
		$this->validateIfExists($data, self::ITEMS, Validate::Array);
		$this->validateIfExists($data, self::PARAMETERS, Validate::Array);
	}

	private function parseItem(?string $name = null): array {
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
		return $item;
	}
}
