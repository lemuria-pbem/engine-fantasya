<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Dictionary;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

abstract class AbstractMessage implements Message
{
	use SerializableTrait;

	private static ?bool $isDebug = null;

	public function __toString(): string {
		$dictionary = new Dictionary();
		$key        = 'combat.message.' . getClass($this);
		$message    = $dictionary->get($key);
		return $message === $key ? $this->getDebug() : $this->translate($message);
	}

	#[ArrayShape(['type' => 'string'])]
	public function serialize(): array {
		$data = ['type' => getClass($this)];
		foreach ($this->getParameters() as $key => $value) {
			$data[$key] = $value;
		}
		if ($this->isDebug()) {
			$data['debug'] = $this->getDebug();
		}
		return $data;
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		return $this;
	}

	protected function translate(string $template): string {
		return $template;
	}

	abstract protected function getDebug(): string;

	protected function getParameters(): array {
		return [];
	}

	private function isDebug(): bool {
		if (self::$isDebug === null) {
			self::$isDebug = State::getInstance()->getTurnOptions()->DebugBattles();
		}
		return self::$isDebug;
	}
}
