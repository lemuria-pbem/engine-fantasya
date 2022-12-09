<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class SpyNotRevealedMessage extends AbstractUnitMessage
{
	protected Result $result = Result::EVENT;

	protected function create(): string {
		return 'An unknown unit has tried to spy on us.';
	}
}
