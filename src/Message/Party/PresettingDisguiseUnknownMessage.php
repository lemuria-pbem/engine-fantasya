<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class PresettingDisguiseUnknownMessage extends PresettingDisguisePartyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'We do not know a party ' . $this->party . ' for disguising.';
	}
}
