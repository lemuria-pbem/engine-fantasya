<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptSoldMessage extends AcceptBoughtMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' buys ' . $this->quantity . ' from merchant ' . $this->unit . ' for ' . $this->payment . '.';
	}
}
