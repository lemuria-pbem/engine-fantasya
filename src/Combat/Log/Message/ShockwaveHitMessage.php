<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Validate;

class ShockwaveHitMessage extends AbstractMessage
{
	private const string COMBATANT = 'combatant';

	private const string COUNT = 'count';

	protected array $simpleParameters = [self::COMBATANT, self::COUNT];

	public function __construct(protected ?string $combatant = null, protected ?int $count = null) {
		parent::__construct();
	}

	public function getDebug(): string {
		return $this->count . ' fighters of combatant ' . $this->combatant . ' are distracted by a Shockwave.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->combatant = $data[self::COMBATANT];
		$this->count     = $data[self::COUNT];
		return $this;
	}

	protected function getParameters(): array {
		return [self::COMBATANT => $this->combatant, self::COUNT => $this->count];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$fighter = $this->dictionary->get('combat.fighter', $this->count > 1 ? 1 : 0);
		$message = str_replace('$fighter', $fighter, $message);
		$will = $this->dictionary->get('combat.will', $this->count > 1 ? 1 : 0);
		return str_replace('$will', $will, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::COMBATANT, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
	}
}
