<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;
use Lemuria\Validate;

abstract class AbstractSplitWeaponMessage extends AbstractSplitMessage
{
	private const WEAPON = 'weapon';

	protected string $weapon;

	public function __construct(protected ?Entity $unit = null, ?Combatant $from = null, ?Combatant $to = null,
										protected ?int    $count = null, protected ?int $battleRow = null) {
		parent::__construct($unit, $from, $to, $count, $battleRow);
		if ($to) {
			$this->weapon = getClass($to->Weapon());
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
