<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class RobOwnUnitMessage extends RobNotFoundMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot rob unit ' . $this->unit . ' of own party.';
	}
}
