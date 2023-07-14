<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class NameRealmNotFoundMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Name of realm cannot be changed.';
	}
}
