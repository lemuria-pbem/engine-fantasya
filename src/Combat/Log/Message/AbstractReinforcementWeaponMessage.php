<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;
use Lemuria\Validate;

abstract class AbstractReinforcementWeaponMessage extends AbstractReinforcementMessage
{
	private const WEAPON = 'weapon';

	protected string $weapon;

	public function __construct(protected ?Entity $unit = null, ?Combatant $combatant = null,
		                                protected ?int    $count = null, protected ?int $battleRow = null) {
		parent::__construct($unit, $combatant, $count, $battleRow);
		if ($combatant) {
			$this->weapon = getClass($combatant->Weapon());
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->weapon = $data[self::WEAPON];
		return $this;
	}

	protected function getParameters(): array {
		$parameters               = parent::getParameters();
		$parameters[self::WEAPON] = $this->weapon;
		return $parameters;
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$weapon  = $this->translateSingleton($this->weapon, $this->count > 1 ? 1 : 0);
		return str_replace('$weapon', $weapon, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::WEAPON, Validate::String);
	}
}
