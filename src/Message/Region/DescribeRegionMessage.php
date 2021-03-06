<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message;

class DescribeRegionMessage extends AbstractRegionMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Region ' . $this->id . ' now has a new description.';
	}
}
