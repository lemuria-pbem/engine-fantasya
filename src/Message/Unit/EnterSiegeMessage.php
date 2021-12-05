<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class EnterSiegeMessage extends EnterAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot enter the sieged construction ' . $this->construction . '.';
	}
}
