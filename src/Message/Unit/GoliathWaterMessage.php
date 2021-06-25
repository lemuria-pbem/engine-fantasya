<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GoliathWaterMessage extends AbstractUnitApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' feels strong as a horse.';
	}
}
