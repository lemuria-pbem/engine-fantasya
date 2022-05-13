<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class GazeOfTheGriffinMessage extends GazeOfTheGriffinAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' beholds region ' . $this->region . '.';
	}
}
