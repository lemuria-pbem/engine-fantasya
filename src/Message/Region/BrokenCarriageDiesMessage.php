<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message;

class BrokenCarriageDiesMessage extends BrokenCarriageMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'The ragged orc dies, leaving behind his horses, carriage and its strange payload.';
	}
}
