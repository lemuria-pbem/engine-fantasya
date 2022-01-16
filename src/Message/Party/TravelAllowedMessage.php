<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class TravelAllowedMessage extends TravelGuardMessage
{
	protected function create(): string {
		return 'Our guards have allowed unit ' . $this->unit . ' in region ' . $this->region . ' to pass.';
	}
}
