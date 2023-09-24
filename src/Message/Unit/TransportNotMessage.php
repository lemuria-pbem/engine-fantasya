<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TransportNotMessage extends TransportMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will not transport realm goods.';
	}
}
