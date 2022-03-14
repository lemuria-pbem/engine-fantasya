<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

class BattleSpellNoAuraMessage extends AbstractMessage
{
	use BuilderTrait;

	protected Entity $unit;

	protected array $simpleParameters = ['unit'];

	#[Pure] public function __construct(?Unit $unit = null, protected ?BattleSpell $spell = null) {
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' has not enough Aura to cast ' . $this->spell . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data['id'], $data['name']);
		/** @var BattleSpell $spell */
		$spell       = self::createSpell($data['spell']);
		$this->spell = $spell;
		return $this;
	}

	#[ArrayShape(['unit' => 'int', 'name' => 'string', 'spell' => 'string'])]
	#[Pure] protected function getParameters(): array {
		return ['unit' => $this->unit->id->Id(), 'name' => $this->unit->name, 'spell' => $this->spell];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$spell   = parent::dictionary()->get('spell', $this->spell);
		return str_replace('$spell', $spell, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'unit', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'spell', 'string');
	}
}
