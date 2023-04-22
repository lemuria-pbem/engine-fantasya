<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class CarcassMessage extends CarcassOnlyMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' takes ' . $this->item . ' from ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
