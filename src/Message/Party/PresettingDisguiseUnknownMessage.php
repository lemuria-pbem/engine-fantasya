<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class PresettingDisguiseUnknownMessage extends PresettingDisguisePartyMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'We do not know a party ' . $this->party . ' for disguising.';
	}
}
