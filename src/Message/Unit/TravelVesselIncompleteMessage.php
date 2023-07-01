<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelVesselIncompleteMessage extends TravelNotCaptainMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot command the unfinished vessel ' . $this->vessel . ' yet.';
	}
}
