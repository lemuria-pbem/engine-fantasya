<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class UpkeepPayOnlyMessage extends UpkeepPayMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only pay ' . $this->upkeep . ' upkeep for construction ' . $this->construction . '.';
	}
}
