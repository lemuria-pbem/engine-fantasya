<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class FavorableWindsNoMessage extends AbstractCastMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not on a ship to cast Favorable Winds.';
	}
}
