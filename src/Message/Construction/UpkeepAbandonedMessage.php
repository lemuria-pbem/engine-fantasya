<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class UpkeepAbandonedMessage extends AbstractConstructionMessage
{
	protected function create(): string {
		return 'Construction ' . $this->id . ' is abandoned, nobody has paid its upkeep.';
	}
}
