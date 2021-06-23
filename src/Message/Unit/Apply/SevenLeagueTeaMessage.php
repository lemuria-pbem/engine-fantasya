<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

class SevenLeagueTeaMessage extends AbstractApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' gets fast as the wind.';
	}
}
