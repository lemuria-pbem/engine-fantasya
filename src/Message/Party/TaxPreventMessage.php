<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class TaxPreventMessage extends AbstractPreventMessage
{
	protected function createActivity(): string {
		return 'collecting taxes';
	}
}
