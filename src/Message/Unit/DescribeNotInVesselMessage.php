<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class DescribeNotInVesselMessage extends DescribeNotInConstructionMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not in any vessel and thus cannot describe it.';
	}
}
