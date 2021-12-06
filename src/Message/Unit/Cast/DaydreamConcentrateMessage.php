<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;

class DaydreamConcentrateMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot concentrate any more.';
	}
}
