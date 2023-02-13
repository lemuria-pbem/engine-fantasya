<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelUnpaidCanalMessage extends TravelTooHeavyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot pay the canal fee.';
	}
}
