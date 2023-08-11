<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;
use Lemuria\Validate;

abstract class AbstractSplitMessage extends AbstractMessage
{
	private const ID = 'id';

	private const UNIT = 'unit';

	private const NAME = 'name';

	private const FROM = 'from';

	private const TO = 'to';

	private const COUNT = 'count';

	private const BATTLE_ROW = 'battleRow';

	protected array $simpleParameters = [self::COUNT, self::FROM, self::TO, self::UNIT];

	protected string $from;

	protected string $to;

	public function __construct(protected ?Entity $unit = null, ?Combatant $from = null, ?Combatant $to = null,
		                        protected ?int $count = null, protected ?int $battleRow = null) {
		parent::__construct();
		if ($from) {
			$this->from = $from->Id();
		}
		if ($to) {
			$this->to = $to->Id();
		}
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit      = Entity::create($data[self::ID], $data[self::NAME]);
		$this->from      = $data[self::FROM];
		$this->to        = $data[self::TO];
		$this->count     = $data[self::COUNT];
		$this->battleRow = $data[self::BATTLE_ROW];
		return $this;
	}

	protected function getParameters(): array {
		return [
			self::UNIT => $this->unit->id->Id(), self::NAME => $this->unit->name, self::FROM => $this->from,
			self::TO => $this->to, self::COUNT => $this->count, self::BATTLE_ROW => $this->battleRow
		];
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

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::FROM, Validate::String);
		$this->validate($data, self::TO, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
		$this->validate($data, self::BATTLE_ROW, Validate::Int);
	}
}
