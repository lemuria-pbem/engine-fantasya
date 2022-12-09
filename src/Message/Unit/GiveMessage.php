<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class GiveMessage extends GiveRejectedMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' gives ' . $this->gift . ' to unit ' . $this->recipient . '.';
	}
}
