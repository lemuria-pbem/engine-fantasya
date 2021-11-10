<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;

abstract class AbstractSplitWeaponMessage extends AbstractSplitMessage
{
	protected string $weapon;

	#[Pure] public function __construct(protected ?Entity $unit = null, ?Combatant $from = null, ?Combatant $to = null,
										protected ?int    $count = null, protected ?int $battleRow = null) {
		parent::__construct($unit, $from, $to, $count, $battleRow);
		if ($to) {
			$this->weapon = getClass($to->Weapon());
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->weapon = $data['weapon'];
		return $this;
	}

	#[ArrayShape(['id' => 'int', 'name' => 'string', 'from' => 'string', 'to' => 'string', 'count' => 'int', 'battleRow' => 'int', 'weapon' => 'string'])]
	#[Pure] protected function getParameters(): array {
		$parameters           = parent::getParameters();
		$parameters['weapon'] = $this->weapon;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$weapon  = parent::dictionary()->get('combat.weapon.' . $this->weapon, $this->count > 1 ? 1 : 0);
		return str_replace('$weapon', $weapon, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'weapon', 'string');
	}
}