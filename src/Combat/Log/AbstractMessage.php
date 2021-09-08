<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use function Lemuria\number;
use Lemuria\Item;
use Lemuria\Singleton;
use Lemuria\Model\Dictionary;
use Lemuria\SingletonTrait;

abstract class AbstractMessage
{
	use SingletonTrait;

	public function render(): string {
		$this->getData();
		return $this->translate() ?? $this->create();
	}

	abstract protected function create(): string;

	protected function getData(): void {
	}

	protected function translate(): ?string {
		$translation = $this->translateKey('combat.' . getClass($this));
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
			$properties[] = $property->getName();
		}
		return $properties;
	}

	protected function getTranslation(string $name): string {
		return (string)$this->$name;
	}

	protected function commodity(string $property, string $name, int $index = 0): ?string {
		return $this->getTranslatedName($property, $name, 'resource', $index);
	}

	protected function item(string $property, string $name): ?string {
		if ($property === $name) {
			$commodity = getClass($this->$name->Commodity());
			$count     = $this->$name->Count();
			$item      = $this->translateKey('resource.' . $commodity, $count > 1 ? 1 : 0);
			if ($item) {
				return number($count) . ' ' . $item;
			}
		}
		return null;
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
