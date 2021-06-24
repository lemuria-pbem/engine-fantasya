<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Command\Apply;

abstract class AbstractApply
{
	public function __construct(protected Apply $apply) {
	}

	public function CanApply(): bool {
		return true;
	}

	/** @noinspection PhpPureAttributeCanBeAddedInspection */
	public function apply(): int {
		return $this->apply->Count();
	}
}
