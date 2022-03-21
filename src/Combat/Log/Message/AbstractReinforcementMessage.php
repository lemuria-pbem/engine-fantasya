<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;

abstract class AbstractReinforcementMessage extends AbstractMessage
{
	protected array $simpleParameters = ['combatant', 'count', 'unit'];

	protected string $combatant;

	#[Pure] public function __construct(protected ?Entity $unit = null, ?Combatant $combatant = null,
		                                protected ?int    $count = null, protected ?int $battleRow = null) {
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit      = Entity::create($data['unit'], $data['name']);
		$this->combatant = $data['combatant'];
		$this->count     = $data['count'];
		$this->battleRow = $data['battleRow'];
		return $this;
	}

	#[ArrayShape(['unit' => 'int', 'name' => 'string', 'combatant' => 'null|string', 'count' => 'int', 'battleRow' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['unit'  => $this->unit->id->Id(), 'name'      => $this->unit->name, 'combatant' => $this->combatant,
			    'count' => $this->count,          'battleRow' => $this->battleRow];
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$fighter   = parent::dictionary()->get('combat.fighter', $this->count > 1 ? 1 : 0);
		$message   = str_replace('$fighter', $fighter, $message);
		$battleRow = parent::dictionary()->get('combat.battleRow.' . Combat::ROW_NAME[$this->battleRow]);
		return str_replace('$battleRow', $battleRow, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'unit', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'combatant', 'string');
		$this->validate($data, 'count', 'int');
		$this->validate($data, 'battleRow', 'int');
	}
}
