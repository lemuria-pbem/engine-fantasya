<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DevastateUnsupportedMessage extends TakeUnsupportedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot devastate ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
