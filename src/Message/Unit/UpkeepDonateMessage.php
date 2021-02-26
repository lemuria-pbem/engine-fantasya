<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class UpkeepDonateMessage extends UpkeepCharityMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' donates ' . $this->upkeep . ' upkeep for construction ' . $this->construction . ' to unit ' . $this->unit . '.';
	}
}
