<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class NoMoveMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'The server has not received orders for party ' . $this->id . ' from you this turn.';
	}
}
