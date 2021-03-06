<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

abstract class AbstractNoDemandMessage extends AbstractUnitMessage
{
	protected string $level = Message::DEBUG;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot ' . $this->createActivity() . ', no demand.';
	}

	abstract protected function createActivity(): string;
}
