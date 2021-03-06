<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class NextMessage extends AbstractPartyMessage
{
	protected function create(): string {
		return 'Finishing turn for party ' . $this->id . '.';
	}
}
