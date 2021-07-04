<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Command\Cast;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;

abstract class AbstractCast
{
	use MessageTrait;

	public function __construct(protected Cast $cast) {
	}

	abstract public function cast(): void;
}
