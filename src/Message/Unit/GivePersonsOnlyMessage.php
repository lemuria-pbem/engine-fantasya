<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class GivePersonsOnlyMessage extends GivePersonsMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only transfer ' . $this->persons . ' persons to unit ' . $this->recipient . '.';
	}
}
