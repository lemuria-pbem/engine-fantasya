<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class RingOfInvisibilityGoldRingMessage extends AbstractCastMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no Gold Ring to cast Ring of Invisibility.';
	}
}
