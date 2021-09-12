<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use JetBrains\PhpStorm\Pure;

use Lemuria\Id;
use Lemuria\Entity as EntityModel;

final class Entity implements \Stringable
{
	public Id $id;

	public string $name;

	public static function create(int $id, string $name): Entity {
		$entity       = new self();
		$entity->id   = new Id($id);
		$entity->name = $name;
		return $entity;
	}

	#[Pure] public function __construct(?EntityModel $entity = null) {
		if ($entity) {
			$this->id   = $entity->Id();
			$this->name = $entity->Name();
		}
	}

	public function __toString(): string {
		return $this->name . ' [' . $this->id . ']';
	}
}
