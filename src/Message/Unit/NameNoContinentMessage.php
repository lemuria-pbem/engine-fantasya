<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class NameNoContinentMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not on a continent to name.';
	}
}
