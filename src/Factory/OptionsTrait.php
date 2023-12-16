<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\isClass;
use Lemuria\Id;

trait OptionsTrait
{
	protected array $options;

	protected function hasOption(string $name): bool {
		return isset($this->options[$name]);
	}

	protected function getIdOption(string $name): Id {
		$option = $this->options[$name] ?? null;
		if (is_numeric($option)) {
			return new Id((int)round($option));
		}
		if (is_string($option)) {
			return Id::fromId($option);
		}
		throw new \InvalidArgumentException('Expected ID option "' . $name . '".');
	}

	protected function getOption(string $name, string $type): mixed {
		$option = $this->options[$name] ?? null;
		if (isClass($type)) {
			if ($option instanceof $type) {
				return $option;
			}
		} else {
			$isType = 'is_' . $type;
			if ($isType($option)) {
				return $option;
			}
		}
		throw new \InvalidArgumentException('Expected ' . $type . ' option "' . $name . '".');
	}
}
