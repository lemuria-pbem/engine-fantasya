<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class DescribeRegionMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Region ' . $this->id . ' now has a new description.';
	}
}
