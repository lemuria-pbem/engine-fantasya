<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DescribeNotInVesselMessage extends DescribeNotInConstructionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not in any vessel and thus cannot describe it.';
	}
}
