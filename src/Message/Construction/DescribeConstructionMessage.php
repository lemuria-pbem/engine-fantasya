<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message\Result;

class DescribeConstructionMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Construction ' . $this->id . ' now has a new description.';
	}
}
