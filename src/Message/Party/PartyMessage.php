<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class PartyMessage extends AbstractPartyMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Starting turn for party ' . $this->id . '.';
	}
}
