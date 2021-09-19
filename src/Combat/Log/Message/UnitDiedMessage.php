<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

class UnitDiedMessage extends AbstractMessage
{
	protected Entity $unit;

	#[Pure] public function __construct(?Unit $unit = null) {
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function getDebug(): string {
		return $this->unit . ' is destroyed.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data['id'], $data['name']);
		return $this;
	}

	#[ArrayShape(['id' => 'int', 'name' => 'string'])]
	#[Pure]	protected function getParameters(): array {
		return ['id' => $this->unit->id, 'name'  => $this->unit->name];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'name', 'string');
	}
}
