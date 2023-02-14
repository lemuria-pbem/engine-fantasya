<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BoardUnpaidDemurrageMessage extends BoardDeniedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot board the vessel ' . $this->vessel . ' because demurrage has not been paid.';
	}
}
