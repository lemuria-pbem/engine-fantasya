<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Validate;

abstract class AbstractReinforcementMessage extends AbstractMessage
{
	private const string COMBATANT = 'combatant';

	private const string COUNT = 'count';

	private const string UNIT = 'unit';

	private const string BATTLE_ROW = 'battleRow';

	private const string NAME = 'name';

	protected array $simpleParameters = [self::COMBATANT, self::COUNT, self::UNIT];

	protected string $combatant;

	public function __construct(protected ?Entity $unit = null, ?Combatant $combatant = null,
		                        protected ?int $count = null, protected ?int $battleRow = null) {
		parent::__construct();
		if ($combatant) {
			$this->combatant = $combatant->Id();
		}
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->unit      = Entity::create($data[self::UNIT], $data[self::NAME]);
		$this->combatant = $data[self::COMBATANT];
		$this->count     = $data[self::COUNT];
		$this->battleRow = $data[self::BATTLE_ROW];
		return $this;
	}

	protected function getParameters(): array {
		return [
			self::UNIT       => $this->unit->id->Id(), self::NAME => $this->unit->name,
			self::COMBATANT  => $this->combatant, self::COUNT     => $this->count,
			self::BATTLE_ROW => $this->battleRow];
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$fighter   = $this->dictionary->get('combat.fighter', $this->count > 1 ? 1 : 0);
		$message   = str_replace('$fighter', $fighter, $message);
		$battleRow = $this->dictionary->get('combat.battleRow.' . Combat::ROW_NAME[$this->battleRow]);
		return str_replace('$battleRow', $battleRow, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::COMBATANT, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
		$this->validate($data, self::BATTLE_ROW, Validate::Int);
	}
}
