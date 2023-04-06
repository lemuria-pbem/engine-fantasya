<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TradeNotOursMessage extends TradeNotFoundMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot change foreign trade ' . $this->trade . '.';
	}
}
