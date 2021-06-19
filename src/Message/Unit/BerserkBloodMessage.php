<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BerserkBloodMessage extends AbstractUnitApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' suddenly feels bloodlust.';
	}
}
