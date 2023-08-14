<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Id;
use Lemuria\Entity as EntityModel;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

final class Entity implements \Stringable, Serializable
{
	use SerializableTrait;

	private const ID = 'id';

	private const NAME = 'name';

	public Id $id;

	public string $name;

	public static function create(int $id, string $name): Entity {
		$entity       = new self();
		$entity->id   = new Id($id);
		$entity->name = $name;
		return $entity;
	}

	public function __construct(?EntityModel $entity = null) {
		if ($entity) {
			$this->id   = $entity->Id();
			$this->name = $entity->Name();
		}
	}

	public function __toString(): string {
		return $this->name . ' [' . $this->id . ']';
	}

	public function serialize(): array {
		return [self::ID => $this->id->Id(), self::NAME => $this->name];
	}

	public function unserialize(array $data): static {
		$this->validateSerializedData($data);
		$this->id   = new Id($data[self::ID]);
		$this->name = $data[self::NAME];
		return $this;
	}

	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::ID, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
	}
}
