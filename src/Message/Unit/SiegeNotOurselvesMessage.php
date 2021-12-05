<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SiegeNotOurselvesMessage extends SiegeNotFightingMessage
{
	protected function create(): string {
		return 'We will not siege ourselves.';
	}
}
