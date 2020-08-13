<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Construction;

use Lemuria\Engine\Message;

class DescribeMessage extends AbstractConstructionMessage
{
	protected string $level = Message::SUCCESS;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Construction ' . $this->id . ' now has a new description.';
	}
}
