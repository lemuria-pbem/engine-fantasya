<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class GuardMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' guards the region.';
	}
}
