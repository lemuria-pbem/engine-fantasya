<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class RobFailsMessage extends RobSelfMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'The attempted robbery fails as the victim is defending itself.';
	}
}
