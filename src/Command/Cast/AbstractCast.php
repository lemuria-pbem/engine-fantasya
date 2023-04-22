<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Command\Cast;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Model\Domain;

abstract class AbstractCast
{
	use MessageTrait;

	public function __construct(protected Cast $cast) {
	}

	public function getReassignmentDomain(): ?Domain {
		return $this->cast->Target() ? Domain::Unit : null;
	}

	public function getReassignmentParameter(): int {
		return $this->cast->Phrase()->count();
	}

	abstract public function cast(): void;
}
