<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class SpyNotRevealedMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'An unknown unit has tried to spy on us.';
	}
}
