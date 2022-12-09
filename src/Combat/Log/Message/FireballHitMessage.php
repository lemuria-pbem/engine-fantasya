<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;
use Lemuria\Validate;

class FireballHitMessage extends AbstractMessage
{
	private const FIGHTER = 'fighter';

	private const DAMAGE = 'damage';

	protected array $simpleParameters = [self::FIGHTER, self::DAMAGE];

	public function __construct(protected ?string $fighter = null, protected ?int $damage = null) {
	}

	public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is hit by a Fireball and receives ' . $this->damage . '.';
	}

	public function unserialize(array $data): Serializable {
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
		$hitpoint = parent::dictionary()->get('combat.hitpoint', $this->damage > 1 ? 1 : 0);
		return str_replace('$hitpoint', $hitpoint, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::FIGHTER, Validate::String);
		$this->validate($data, self::DAMAGE, Validate::Int);
	}
}
