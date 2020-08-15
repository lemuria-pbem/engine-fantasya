<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Message;

class DescribeRegionMessage extends AbstractRegionMessage
{
	protected string $level = Message::SUCCESS;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Region ' . $this->id . ' now has a new description.';
	}
}
