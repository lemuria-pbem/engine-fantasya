<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Serializable;
use Lemuria\Validate;

class CombatantNoWeaponMessage extends AbstractMessage
{
	private const COMBATANT = 'combatant';

	private const COUNT = 'count';

	private const FIGHTER = 'fighter';

	private const BATTLE_ROW = 'battleRow';

	protected array $simpleParameters = [self::COMBATANT, self::COUNT];

	protected string $combatant;

	protected int $count;

	protected ?string $fighter = null;

	protected int $battleRow;

	public function __construct(?Combatant $combatant = null) {
		parent::__construct();
		if ($combatant) {
			$this->combatant = $combatant->Id();
			$this->count     = $combatant->Size();
			$this->fighter   = (string)$combatant->Unit()->Race();
			$this->battleRow = $combatant->BattleRow()->value;
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->combatant = $data[self::COMBATANT];
		$this->count     = $data[self::COUNT];
		$this->fighter   = $data[self::FIGHTER] ?? null;
		$this->battleRow = $data[self::BATTLE_ROW];
		return $this;
	}

	public function getDebug(): string {
		$fighter = $this->fighter ?: 'fighter';
		return $this->combatant . ', ' . $this->count .' ' . $fighter . ', ' . Combat::ROW_NAME[$this->battleRow] . ' row';
	}

	protected function getParameters(): array {
		return [
			self::COMBATANT  => $this->combatant, self::COUNT => $this->count, self::FIGHTER => $this->fighter,
			self::BATTLE_ROW => $this->battleRow
		];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		if ($this->fighter) {
			$fighter = $this->translateSingleton($this->fighter, $this->count > 1 ? 1 : 0);
		} else {
			$fighter = $this->dictionary->get('combat.fighter', $this->count > 1 ? 1 : 0);
		}
		$message   = str_replace('$fighter', $fighter, $message);
		$battleRow = $this->dictionary->get('battleRow.' . $this->battleRow, $this->count > 1 ? 1 : 0);
		return str_replace('$battleRow', $battleRow, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::COMBATANT, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
		$this->validateIfExists($data, self::FIGHTER, Validate::StringOrNull);
		$this->validate($data, self::BATTLE_ROW, Validate::Int);
	}
}
