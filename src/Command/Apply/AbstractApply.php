<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\Use\Apply;

abstract class AbstractApply
{
	public function __construct(protected Apply $apply) {
	}

	public function CanApply(): bool {
		return true;
	}

	#[Pure] public function apply(): int {
		return $this->apply->Count();
	}
}
