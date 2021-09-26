<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;

abstract class AbstractSplitMessage extends AbstractMessage
{
	protected array $simpleParameters = ['count', 'from', 'to', 'unit'];

	protected string $from;

	protected string $to;

	#[Pure] public function __construct(protected ?Entity $unit = null, ?Combatant $from = null, ?Combatant $to = null,
										protected ?int    $count = null, protected ?int $battleRow = null) {
		if ($from) {
			$this->from = $from->Id();
		}
		if ($to) {
			$this->to = $to->Id();
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit      = Entity::create($data['id'], $data['name']);
		$this->from      = $data['from'];
		$this->to        = $data['to'];
		$this->count     = $data['count'];
		$this->battleRow = $data['battleRow'];
		return $this;
	}

	#[ArrayShape(['id' => 'int', 'name' => 'string', 'from' => 'string', 'to' => 'string', 'count' => 'int', 'battleRow' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['id' => $this->unit->id->Id(), 'name'  => $this->unit->name, 'from'      => $this->from,
			    'to' => $this->to,             'count' => $this->count,      'battleRow' => $this->battleRow];
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
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'from', 'string');
		$this->validate($data, 'to', 'string');
		$this->validate($data, 'count', 'int');
		$this->validate($data, 'battleRow', 'int');
	}
}
