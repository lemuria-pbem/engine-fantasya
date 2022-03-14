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
	protected array $simpleParameters = ['unit'];

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

	#[ArrayShape(['unit' => 'int', 'name' => 'string'])]
	#[Pure]	protected function getParameters(): array {
		return ['unit' => $this->unit->id->Id(), 'name'  => $this->unit->name];
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'unit', 'int');
		$this->validate($data, 'name', 'string');
	}
}
