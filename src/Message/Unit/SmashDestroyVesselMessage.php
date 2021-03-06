<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashDestroyVesselMessage extends SmashLeaveVesselMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has destroyed vessel ' . $this->vessel . '.';
	}
}
