<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SevenLeagueTeaMessage extends AbstractUnitApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' gets fast as the wind.';
	}
}
