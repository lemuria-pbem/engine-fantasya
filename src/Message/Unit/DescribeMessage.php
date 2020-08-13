<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class DescribeMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' now has a new description.';
	}
}
