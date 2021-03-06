<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TaxNoDemandMessage extends AbstractNoDemandMessage
{
	protected function createActivity(): string {
		return 'collect taxes';
	}
}
