<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Container;
use function Lemuria\getClass;
use function Lemuria\number;
use Lemuria\Item;
use Lemuria\Singleton;
use Lemuria\Engine\Report;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Model\Dictionary;
use Lemuria\SingletonTrait;

abstract class AbstractMessage implements MessageType
{
	use SingletonTrait;

	protected string $level = Message::DEBUG;

	protected Section $section = Section::EVENT;

	protected Id $id;

	#[ExpectedValues(valuesFromClass: Report::class)]
	#[Pure] public function Level(): string {
		return $this->level;
	}

	public function Section(): Section {
		return $this->section;
	}

	public function render(LemuriaMessage $message): string {
		$this->getData($message);
		return $this->translate() ?? $this->create();
	}

	abstract protected function create(): string;

	protected function getData(LemuriaMessage $message): void {
		$this->id = $message->Assignee();
	}

	protected function translate(): ?string {
		$translation = $this->translateKey('message.' . getClass($this));
		if ($translation === null) {
			return null;
		}
		while (preg_match('/({[^:]+:\$[a-zA-Z]+})+/', $translation, $matches) === 1) {
			$match = $matches[1];
			$translation = str_replace($match, $this->replacePrefix($match), $translation);
		}
		while (preg_match('/({\$[^:]+:[^}]+})+/', $translation, $matches) === 1) {
			$match = $matches[1];
			$translation = str_replace($match, $this->replaceSuffix($match), $translation);
		}
		while (preg_match('/({[^=]+=\$[a-zA-Z]+})+/', $translation, $matches) === 1) {
			$match = $matches[1];
			$translation = str_replace($match, $this->replace($match), $translation);
		}
		foreach ($this->getVariables() as $name) {
			$translation = str_replace('$' . $name, $this->getTranslation($name), $translation);
		}
		return $translation;
	}

	protected function translateKey(string $keyPath, ?int $index = null): ?string {
		$dictionary  = new Dictionary();
		$translation = $dictionary->get($keyPath, $index);
		if ($index !== null) {
			$keyPath .= '.' . $index;
		}
		return $translation === $keyPath ? null : $translation;
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

	protected function getTranslation(string $name): string {
		$translation = $this->$name;
		if ($translation instanceof \BackedEnum) {
			$translation = $translation->value;
		}
		return (string)$translation;
	}

	protected function building(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'building');
	}

	protected function commodity(string $property, string $name, int $index = 0): ?string {
		return $this->getTranslatedName($property, $name, 'resource', $index);
	}

	protected function composition(string $property, string $name, int $index = 0): ?string {
		return $this->getTranslatedName($property, $name, 'composition', $index);
	}

	protected function item(string $property, string $name): ?string {
		if ($property === $name) {
			$commodity = $this->$name->Commodity();
			if ($commodity instanceof Container) {
				return $this->translateKey('kind.' . $commodity->Type()->name);
			}

			$resource = getClass($commodity);
			$count    = $this->$name->Count();
			$item     = $this->translateKey('resource.' . $resource, $count > 1 ? 1 : 0);
			if ($item) {
				return $count < PHP_INT_MAX ? number($count) . ' ' . $item : $item;
			}
		}
		return null;
	}

	protected function landscape(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'landscape');
	}

	protected function ship(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'ship');
	}

	protected function talent(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'talent');
	}

	protected function spell(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'spell');
	}

	protected function direction(string $property, string $name): ?string {
		return $this->getTranslatedName($property, $name, 'world.short');
	}

	#[Pure] protected function number(string $property, string $name): ?string {
		return $property === $name ? number($this->$name) : null;
	}

	private function getTranslatedName(string $property, string $name, string $prefix, ?int $index = null): ?string {
		if ($property === $name) {
			$class = getClass($this->$name);
			$class = $this->translateKey($prefix . '.' . $class, $index);
			if ($class) {
				return $class;
			}
		}
		return null;
	}

	private function replacePrefix(string $match): string {
		$parts    = explode(':', substr($match, 1, strlen($match) - 2));
		$key      = $parts[0];
		$name     = substr($parts[1], 1);
		$variable = $this->$name;
		if ($variable instanceof Singleton) {
			return $this->translateKey('replace.' . $key . '.' . getClass($variable)) . ' ' . $parts[1];
		}
		if ($variable instanceof Item) {
			return $this->translateKey('replace.' . $key, $variable->Count() === 1 ? 0 : 1) . ' ' . $parts[1];
		}
		if (is_int($variable)) {
			return $this->translateKey('replace.' . $key, $variable === 1 ? 0 : 1) . ' ' . $parts[1];
		}
		return '{' . $parts[0] . '}' . ' ' . $parts[1];
	}

	private function replaceSuffix(string $match): string {
		$parts    = explode(':', substr($match, 1, strlen($match) - 2));
		$name     = substr($parts[0], 1);
		$key      = $parts[1];
		$variable = $this->$name;
		if (is_int($variable)) {
			return $parts[0] . ' ' . $this->translateKey('replace.' . $key, $variable === 1 ? 0 : 1);
		}
		if ($variable instanceof Item) {
			return $parts[0] . ' ' . $this->translateKey('replace.' . $key, $variable->Count() === 1 ? 0 : 1);
		}
		return $parts[0] . ' ' . '{' . $parts[1] . '}';
	}

	private function replace(string $match): string {
		$parts    = explode('=', substr($match, 1, strlen($match) - 2));
		$key      = $parts[0];
		$name     = substr($parts[1], 1);
		$variable = $this->$name;
		if ($variable instanceof Singleton) {
			return $this->translateKey('replace.' . $key . '.' . getClass($variable));
		}
		return '{' . $parts[0] . '}' . ' ' . $parts[1];
	}
}
