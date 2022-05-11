<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class AstralPassageVesselMessage extends AstralPassageRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' teleports in construction ' . $this->target . '.';
	}
}
