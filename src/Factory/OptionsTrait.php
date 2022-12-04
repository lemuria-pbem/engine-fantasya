<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\isClass;

trait OptionsTrait
{
	protected array $options;

	protected function hasOption(string $name): bool {
		return isset($this->options[$name]);
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
