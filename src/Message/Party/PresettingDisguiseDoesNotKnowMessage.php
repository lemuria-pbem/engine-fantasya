<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class PresettingDisguiseDoesNotKnowMessage extends PresettingDisguisePartyMessage
{
	protected string $level = Message::DEBUG;

	protected function create(): string {
		return 'We do not know party ' . $this->party . ' for disguising.';
	}
}
