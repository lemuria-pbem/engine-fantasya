<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;

class DescribeVesselMessage extends AbstractVesselMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' now has a new description.';
	}
}
