<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;

class DescribeVesselMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' now has a new description.';
	}
}
