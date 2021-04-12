<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

trait OptionsTrait
{
	protected array $options;

	protected function getOption(string $name, string $type): mixed {
		$option = $this->options[$name] ?? null;
		$isType = 'is_' . $type;
		if (!$isType($option)) {
			throw new \InvalidArgumentException('Expected ' . $type . ' option "' . $name . '".');
		}
		return $option;
	}
}
