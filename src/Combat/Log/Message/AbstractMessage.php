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

	protected static ?Dictionary $dictionary = null;

	/**
	 * @var string[]
	 */
	protected array $simpleParameters = [];

	public function __toString(): string {
		$key     = 'combat.message.' . getClass($this);
		$message = self::dictionary()->get($key);
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

	protected static function dictionary(): Dictionary {
		if (!self::$dictionary) {
			self::$dictionary = new Dictionary();
		}
		return self::$dictionary;
	}

	protected function translate(string $template): string {
		$message = $template;
		foreach ($this->simpleParameters as $name) {
			$message = str_replace('$' . $name, (string)$this->$name, $message);
		}
		return $message;
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
