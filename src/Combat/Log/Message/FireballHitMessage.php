<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

class FireballHitMessage extends AbstractMessage
{
	protected array $simpleParameters = ['fighter', 'damage'];

	#[Pure] public function __construct(protected ?string $fighter = null, protected ?int $damage = null) {
	}

	#[Pure] public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is hit by a Fireball and receives ' . $this->damage . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->fighter = $data['fighter'];
		$this->damage  = $data['damage'];
		return $this;
	}

	#[ArrayShape(['fighter' => 'string', 'damage' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['fighter' => $this->fighter, 'damage' => $this->damage];
	}

	protected function translate(string $template): string {
		$message  = parent::translate($template);
		$hitpoint = parent::dictionary()->get('combat.hitpoint', $this->damage > 1 ? 1 : 0);
		return str_replace('$hitpoint', $hitpoint, $message);
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'fighter', 'string');
		$this->validate($data, 'damage', 'int');
	}
}
