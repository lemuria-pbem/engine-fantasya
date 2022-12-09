<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class NoMoveMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'The server has not received orders for party ' . $this->id . ' from you this turn.';
	}
}
