<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class HelpPartyUnknownMessage extends HelpPartyMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot set a relation to unknown party ' . $this->party . '.';
	}
}
