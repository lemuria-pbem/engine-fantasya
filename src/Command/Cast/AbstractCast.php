<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Command\Cast;

abstract class AbstractCast
{
	public function __construct(protected Cast $cast) {
	}

	abstract public function cast(): void;
}
