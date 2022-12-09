<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class TeleportationErrorMessage extends TeleportationForeignMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teleport foreign unit ' . $this->unit . ' - its treasury is too heavy.';
	}
}
