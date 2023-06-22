<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class RealmDoesNotExistMessage extends RealmAddMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'The realm ' . $this->realm . ' does not exist.';
	}
}
