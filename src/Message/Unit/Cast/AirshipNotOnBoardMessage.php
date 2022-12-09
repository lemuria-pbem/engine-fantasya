<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class AirshipNotOnBoardMessage extends AbstractCastMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be a passenger on a vessel to cast Airship.';
	}
}
