<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;

class ExcessCargoMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is overloaded and takes damage.';
	}
}
