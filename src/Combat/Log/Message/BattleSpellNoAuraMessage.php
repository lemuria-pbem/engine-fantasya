<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;
use Lemuria\Validate;

class BattleSpellNoAuraMessage extends AbstractMessage
{
	use BuilderTrait;

	private const ID = 'id';

	private const UNIT = 'unit';

	private const NAME = 'name';

	private const SPELL = 'spell';

	protected Entity $unit;

	protected array $simpleParameters = [self::UNIT];

	public function __construct(?Unit $unit = null, protected ?BattleSpell $spell = null) {
		parent::__construct();
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' has not enough Aura to cast ' . $this->spell . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data[self::ID], $data[self::NAME]);
		/** @var BattleSpell $spell */
		$spell       = self::createSpell($data[self::SPELL]);
		$this->spell = $spell;
		return $this;
	}

	protected function getParameters(): array {
		return [self::UNIT => $this->unit->id->Id(), self::NAME => $this->unit->name, self::SPELL => (string)$this->spell];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$spell   = $this->dictionary->get('spell', $this->spell);
		return str_replace('$spell', $spell, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::SPELL, Validate::String);
	}
}
