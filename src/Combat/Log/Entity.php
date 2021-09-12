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
