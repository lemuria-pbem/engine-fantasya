<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class DescribePartyMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Party ' . $this->id . ' now has a new description.';
	}
}
