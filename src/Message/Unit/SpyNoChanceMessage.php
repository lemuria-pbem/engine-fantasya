<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class SpyNoChanceMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'We have no chance to spy successfully on any unit.';
	}
}
