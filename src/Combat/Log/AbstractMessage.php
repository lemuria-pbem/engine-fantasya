<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use JetBrains\PhpStorm\ArrayShape;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Battle;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

abstract class AbstractMessage implements Message
{
	use SerializableTrait;

	private static ?bool $isDebug = null;

	public function __construct(Battle $battle) {
	}

	#[ArrayShape(['type' => "string"])]
	public function serialize(): array {
		$data = ['type' => getClass($this)];
		foreach ($this->getParameters() as $key => $value) {
			$data[$key] = $value;
		}
		if ($this->isDebug()) {
			$data['debug'] = (string)$this;
		}
		return $data;
	}

	public function unserialize(array $data): Serializable {
		return $this;
	}

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
