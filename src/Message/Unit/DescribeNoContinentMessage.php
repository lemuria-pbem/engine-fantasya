<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DescribeNoContinentMessage extends NameNoContinentMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not on a continent to describe.';
	}
}
