<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message;

class DescribePartyMessage extends AbstractRegionMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Party ' . $this->id . ' now has a new description.';
	}
}
