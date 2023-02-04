<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

final class Participant implements \Stringable, Serializable
{
	use SerializableTrait;

	private const ID = 'id';

	private const NAME = 'name';

	private const COMBATANTS = 'combatants';

	private const FIGHTERS = 'fighters';

	public int $combatants;

	public int $fighters = 0;

	/**
	 * @param array<Combatant> $combatants
	 */
	public function __construct(public ?Entity $unit = null, array $combatants = []) {
		$this->combatants = count($combatants);
		foreach ($combatants as $combatant) {
			$this->fighters += $combatant->Size();
		}
	}

	public function __toString(): string {
		return $this->unit . ' with ' . $this->fighters . ' fighters in ' . $this->combatants . ' combatants';
	}

	public function serialize(): array {
		return [self::ID         => $this->unit->id->Id(), self::NAME     => $this->unit->name,
				self::COMBATANTS => $this->combatants,     self::FIGHTERS => $this->fighters];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->unit = new Entity();
		$this->unit->unserialize($data);
		$this->combatants = $data[self::COMBATANTS];
		$this->fighters   = $data[self::FIGHTERS];
		return $this;
	}

	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::ID, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::COMBATANTS, Validate::Int);
		$this->validate($data, self::FIGHTERS, Validate::Int);
	}
}
