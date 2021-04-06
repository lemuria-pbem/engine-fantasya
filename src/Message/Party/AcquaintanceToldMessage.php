<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class AcquaintanceToldMessage extends AcquaintanceTellMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' met someone from party ' . $this->party . ' and was told about their people.';
	}
}
