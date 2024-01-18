<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class ThrowOutNotMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'We cannot throw out anyone if we are outside.';
	}
}
