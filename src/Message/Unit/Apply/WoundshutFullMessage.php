<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

class WoundshutFullMessage extends WoundshutMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is healing all its wounds.';
	}
}
