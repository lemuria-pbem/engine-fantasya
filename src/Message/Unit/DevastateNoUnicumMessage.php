<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DevastateNoUnicumMessage extends ReadNoUnicumMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no unicum with ID ' . $this->unicum . '.';
	}
}
