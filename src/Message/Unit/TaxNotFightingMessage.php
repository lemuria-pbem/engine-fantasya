<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TaxNotFightingMessage extends TaxWithoutWeaponMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot enforce tax payment if it does not fight.';
	}
}
