<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class GazeOfTheGriffinNoneMessage extends AbstractCastMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot behold a region in the specified direction.';
	}
}
