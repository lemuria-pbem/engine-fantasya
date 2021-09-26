<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

final class Participant implements \Stringable, Serializable
{
	use SerializableTrait;

	public int $combatants;

	public int $fighters = 0;

	/**
	 * @param Combatant[] $combatants
	 */
	#[Pure] public function __construct(public ?Entity $unit = null, array $combatants = []) {
		$this->combatants = count($combatants);
		foreach ($combatants as $combatant) {
			$this->fighters += $combatant->Size();
		}
	}

	public function __toString(): string {
		return $this->unit . ' with ' . $this->fighters . ' fighters in ' . $this->combatants . ' combatants';
	}

	#[ArrayShape(['id' => "\Lemuria\Id", 'name' => "string", 'combatants' => "int", 'fighters' => "int"])]
	#[Pure] public function serialize(): array {
		return ['id'         => $this->unit->id->Id(), 'name'     => $this->unit->name,
				'combatants' => $this->combatants,     'fighters' => $this->fighters];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->unit       = Entity::create($data['id'], $data['name']);
		$this->combatants = $data['combatants'];
		$this->fighters   = $data['fighters'];
		return $this;
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'combatants', 'int');
		$this->validate($data, 'fighters', 'int');
	}
}
