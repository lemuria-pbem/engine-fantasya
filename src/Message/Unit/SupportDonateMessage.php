<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SupportDonateMessage extends SupportCharityMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' donates ' . $this->support . ' support to unit ' . $this->unit . '.';
	}
}
