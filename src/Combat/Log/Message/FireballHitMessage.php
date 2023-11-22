<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Validate;

class FireballHitMessage extends AbstractMessage
{
	private const string FIGHTER = 'fighter';

	private const string DAMAGE = 'damage';

	protected array $simpleParameters = [self::FIGHTER, self::DAMAGE];

	public function __construct(protected ?string $fighter = null, protected ?int $damage = null) {
		parent::__construct();
	}

	public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is hit by a Fireball and receives ' . $this->damage . '.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->fighter = $data[self::FIGHTER];
		$this->damage  = $data[self::DAMAGE];
		return $this;
	}

	protected function getParameters(): array {
		return [self::FIGHTER => $this->fighter, self::DAMAGE => $this->damage];
	}

	protected function translate(string $template): string {
		$message  = parent::translate($template);
		$hitpoint = $this->dictionary->get('combat.hitpoint', $this->damage > 1 ? 1 : 0);
		return str_replace('$hitpoint', $hitpoint, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::FIGHTER, Validate::String);
		$this->validate($data, self::DAMAGE, Validate::Int);
	}
}
