<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class BrokenCarriageDiesMessage extends BrokenCarriageMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'The ragged orc dies, leaving behind his horses, carriage and its strange payload.';
	}
}
