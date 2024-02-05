<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobOwnPartyMessage extends RobSelfMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot rob own party.';
	}
}
