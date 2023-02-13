<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;

class TravelPortDirectionMessage extends TravelOverLandMessage
{
	protected Result $result = Result::Debug;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' leaves the port ' . $this->direction . '.';
	}
}
