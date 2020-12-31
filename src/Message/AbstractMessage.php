<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Report;
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

	#[ExpectedValues(valuesFromClass: Report::class)]
	#[Pure] public function Level(): string {
		return $this->level;
	}

	public function render(LemuriaMessage $message): string {
		$this->getData($message);
		return  $this->translate() ?? $this->create();
	}

	abstract protected function create(): string;

	protected function getData(LemuriaMessage $message): void {
		$this->id = $message->get();
	}

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
