<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Validate;

class CombatantWeaponSplitMessage extends AbstractMessage
{
	private const string COMBATANT = 'combatant';

	private const string COUNT = 'count';

	private const string NEW_COMBATANT = 'newCombatant';

	protected array $simpleParameters = [self::COMBATANT, self::COUNT, self::NEW_COMBATANT];

	protected string $combatant;

	protected string $newCombatant;

	public function __construct(?Combatant $combatant = null, protected ?int $count = null,
		                        ?Combatant $newCombatant = null) {
		parent::__construct();
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
		if ($newCombatant) {
			$this->newCombatant = $newCombatant->Id();
		}
	}

	public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ' sends ' . $this->count . ' fighters to the new combatant ' . $this->newCombatant . '.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->combatant    = $data[self::COMBATANT];
		$this->count        = $data[self::COUNT];
		$this->newCombatant = $data[self::NEW_COMBATANT];
		return $this;
	}

	protected function getParameters(): array {
		return [self::COMBATANT => $this->combatant, self::COUNT => $this->count, self::NEW_COMBATANT => $this->newCombatant];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$fighter   = $this->dictionary->get('combat.fighter', $this->count > 1 ? 1 : 0);
		return str_replace('$fighter', $fighter, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::COMBATANT, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
		$this->validate($data, self::NEW_COMBATANT, Validate::String);
	}
}
