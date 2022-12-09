<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class SpyNoChanceMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'We have no chance to spy successfully on any unit.';
	}
}
