<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Message\Result;

class PerishMessage extends CreateMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will perish soon.';
	}
}
