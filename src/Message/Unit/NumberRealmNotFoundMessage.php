<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class NumberRealmNotFoundMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'ID of realm cannot be changed.';
	}
}
