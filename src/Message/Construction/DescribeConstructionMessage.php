<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message;

class DescribeConstructionMessage extends AbstractConstructionMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Construction ' . $this->id . ' now has a new description.';
	}
}
