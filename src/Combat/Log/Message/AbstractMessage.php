<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

abstract class AbstractMessage implements Message
{
	use SerializableTrait;

	private const ID = 'id';

	private const TYPE = 'type';

	private const DEBUG = 'debug';

	protected static ?Dictionary $dictionary = null;

	/**
	 * @var array<string>
	 */
	protected array $simpleParameters = [];

	private static ?bool $isDebug = null;

	private ?Id $id = null;

	public function Id(): Id {
		if (!$this->id) {
			$this->id = Lemuria::Report()->nextId();
		}
		return $this->id;
	}

	public function __toString(): string {
		$key     = 'combat.message.' . getClass($this);
		$message = self::dictionary()->get($key);
		return $message === $key ? $this->getDebug() : $this->translate($message);
	}

	public function serialize(): array {
		$data = [self::ID => $this->Id()->Id(), self::TYPE => getClass($this)];
		foreach ($this->getParameters() as $key => $value) {
			$data[$key] = $value;
		}
		if ($this->isDebug()) {
			$data[self::DEBUG] = $this->getDebug();
		}
		return $data;
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->id = new Id($data[self::ID]);
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

	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::ID, Validate::Int);
		$this->validate($data, self::TYPE, Validate::String);
		$this->validateIfExists($data, self::DEBUG, Validate::String);
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
