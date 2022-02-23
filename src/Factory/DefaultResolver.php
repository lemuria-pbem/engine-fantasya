<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command;

class DefaultResolver
{
	/**
	 * @param Command[] $defaults
	 */
	public function __construct(protected array $defaults) {
	}

	/**
	 * @return Command[]
	 */
	public function resolve(): array {
		return $this->defaults; //TODO
	}
}
