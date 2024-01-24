<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ReadNotFoundMessage extends ReadNoUnicumMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know an unicum with number ' . $this->unicum . '.';
	}
}
