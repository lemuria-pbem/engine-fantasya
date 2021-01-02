<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class TaxGuardedMessage extends AbstractGuardedMessage
{
	protected function createActivity(): string {
		return 'collect taxes';
	}
}
