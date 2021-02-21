<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class TravelNoNavigationMessage extends TravelNotCaptainMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not skilled enough in navigation to steer the vessel ' . $this->vessel . '.';
	}
}
