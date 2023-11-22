<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Validate;

abstract class AbstractFleeFromBattleMessage extends AbstractMessage
{
	private const string COMBATANT = 'combatant';

	protected array $simpleParameters = [self::COMBATANT];

	protected string $combatant;

	public function __construct(?Combatant $combatant = null) {
		parent::__construct();
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->combatant = $data[self::COMBATANT];
		return $this;
	}

	protected function getParameters(): array {
		return [self::COMBATANT => $this->combatant];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::COMBATANT, Validate::String);
	}
}
