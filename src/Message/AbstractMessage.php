<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use function Lemuria\getClass;
use function Lemuria\number;
use Lemuria\Item;
use Lemuria\Singleton;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Container;
use Lemuria\SingletonTrait;

abstract class AbstractMessage implements MessageType
{
	use SingletonTrait;

	protected Result $result = Result::Debug;

	protected Section $section = Section::Event;

	protected Reliability $reliability = Reliability::Determined;

	protected Id $id;

	public function Result(): Result {
		return $this->result;
	}

	public function Section(): Section {
		return $this->section;
	}

	public function Reliability(): Reliability {
		return $this->reliability;
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
		while (preg_match('|{g/([a-z]+):([^:]+):\$([a-zA-Z]+)}|', $translation, $matches) === 1) {
			$match       = $matches[0];
			$translation = str_replace($match, $this->replaceGrammar(Casus::from($matches[1]), $matches[2], $matches[3]), $translation);
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

	protected function item(string $property, string $name, ?int $index = null): ?string {
		if ($property === $name) {
			$commodity = $this->$name->Commodity();
			if ($commodity instanceof Container) {
				return $this->translateKey('kind.' . $commodity->Type()->name, $index);
			}

			$resource = getClass($commodity);
			$count    = $this->$name->Count();
			if ($index === null) {
				$index = $count > 1 ? 1 : 0;
			}
			$item = $this->translateKey('resource.' . $resource, $index);
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

	protected function direction(string $property, string $name = 'direction'): ?string {
		return $this->getTranslatedName($property, $name, 'world.short');
	}

	protected function number(string $property, string $name): ?string {
		return $property === $name ? number($this->$name) : null;
	}

	protected function percent(string $property, string $name, int $digits = 0): ?string {
		if ($property === $name) {
			$percent = round(100.0 * $this->$name, $digits);
			if ($digits <= 0) {
				$percent = (int)$percent;
			}
			return str_replace('$p', number($percent), $this->translateKey('replace.percent'));
		}
		return null;
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

	private function replaceGrammar(Casus $casus, string $search, string $name): string {
		$variable = $this->$name;
		if ($variable instanceof Singleton) {
			$singleton = getClass($variable);
		} else {
			$singleton = (string)$variable;
		}
		$index = $casus->index();

		$dictionary = new Dictionary();
		$grammar    = $dictionary->raw('grammar.' . $search);
		$numerus    = $grammar['numerus'];

		$singleton  = $dictionary->raw('singleton.' . $singleton);
		$genus      = $singleton[0];
		$numeri     = $singleton[$index + 1];
		if (is_int($numeri)) {
			$numeri = $singleton[$numeri];
		}

		$replace     = $grammar[$genus][$index];
		$replacement = $numeri[$numerus];
		if (is_int($replacement)) {
			$c           = (int)($replacement / 2) + 1;
			$n           = $replacement % 2;
			$replacement = $singleton[$c][$n];
		}
		return $replace . ' ' . $replacement;
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
