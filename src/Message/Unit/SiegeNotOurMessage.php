<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SiegeNotOurMessage extends SiegeNotFightingMessage
{
	protected function create(): string {
		return 'We will not siege our own construction.';
	}
}
