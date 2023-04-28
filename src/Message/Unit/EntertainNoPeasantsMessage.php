<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class EntertainNoPeasantsMessage extends AbstractNoResourcesMessage
{
	protected function create(): string {
		return 'There are no peasants in region ' . $this->region . ' that could pay for entertaining.';
	}
}
