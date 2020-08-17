<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Message;
use Lemuria\Id;
use Lemuria\Model\Dictionary;
use Lemuria\SingletonTrait;

abstract class AbstractMessage implements MessageType
{
	use SingletonTrait;

	protected string $level = Message::DEBUG;

	protected Id $id;

	/**
	 * @return string
	 */
	public function Level(): string {
		return $this->level;
	}

	/**
	 * @param LemuriaMessage $message
	 * @return string
	 */
	public function render(LemuriaMessage $message): string {
		$this->getData($message);
		return  $this->translate() ?? $this->create();
	}

	/**
	 * @return string
	 */
	abstract protected function create(): string;

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		$this->id = $message->get();
	}

	/**
	 * @return string|null
	 */
	protected function translate(): ?string {
		$dictionary  = new Dictionary();
		$keyPath     = 'message.' . getClass($this);
		$translation = $dictionary->get($keyPath);
		if ($translation === $keyPath) {
			return null;
		}

		foreach ($this->getVariables() as $name) {
			$translation = str_replace('$' . $name, (string)$this->$name, $translation);
		}
		return $translation;
	}

	/**
	 * @return array
	 */
	protected function getVariables(): array {
		$properties = [];
		$reflection = new \ReflectionClass($this);
		foreach ($reflection->getProperties() as $property) {
			$name = $property->getName();
			if ($name !== 'level') {
				$properties[] = $name;
			}
		}
		return $properties;
	}
}
