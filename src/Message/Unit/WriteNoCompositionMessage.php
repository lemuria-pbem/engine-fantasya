<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class WriteNoCompositionMessage extends ReadNoCompositionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no ' . $this->composition . ' with ID ' . $this->unicum . '.';
	}
}
