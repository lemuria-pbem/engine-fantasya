<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Validate;

class AssaultHitMessage extends AbstractMessage
{
	private const string ATTACKER = 'attacker';

	private const string DAMAGE = 'damage';

	private const string DEFENDER = 'defender';

	protected array $simpleParameters = [self::ATTACKER, self::DAMAGE, self::DEFENDER];

	public function __construct(protected ?string $attacker = null, protected ?string $defender = null,
		                        protected ?int $damage = null) {
		parent::__construct();
	}

	public function getDebug(): string {
		return 'Fighter ' . $this->attacker . ' deals ' . $this->damage . ' damage to enemy ' . $this->defender . '.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->attacker = $data[self::ATTACKER];
		$this->defender = $data[self::DEFENDER];
		$this->damage   = $data[self::DAMAGE];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ATTACKER => $this->attacker, self::DEFENDER => $this->defender, self::DAMAGE => $this->damage];
	}

	protected function translate(string $template): string {
		$message  = parent::translate($template);
		$hitpoint = $this->dictionary->get('combat.hitpoint', $this->damage > 1 ? 1 : 0);
		return str_replace('$hitpoint', $hitpoint, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ATTACKER, Validate::String);
		$this->validate($data, self::DEFENDER, Validate::String);
		$this->validate($data, self::DAMAGE, Validate::Int);
	}
}
