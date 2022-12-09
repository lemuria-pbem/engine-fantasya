<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class PresettingDisguiseDoesNotKnowMessage extends PresettingDisguisePartyMessage
{
	protected Result $result = Result::DEBUG;

	protected function create(): string {
		return 'We do not know party ' . $this->party . ' for disguising.';
	}
}
