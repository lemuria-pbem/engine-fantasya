<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Message\Result;

class PickPocketOnlyMessage extends PickPocketMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' picked ' . $this->silver . ' from the pocket of unit ' . $this->enemy . '.';
	}
}
