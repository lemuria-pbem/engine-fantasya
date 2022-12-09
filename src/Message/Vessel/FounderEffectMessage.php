<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;

class FounderEffectMessage extends AbstractVesselMessage
{
	protected Result $result = Result::EVENT;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is too heavy and will take damage if excess payload is not thrown overboard.';
	}
}
