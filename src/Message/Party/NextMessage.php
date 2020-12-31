<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

class NextMessage extends AbstractPartyMessage
{
	protected function create(): string {
		return 'Finishing turn for party ' . $this->id . '.';
	}
}
