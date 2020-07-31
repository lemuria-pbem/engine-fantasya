<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class TempMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'New unit ' . $this->Id() . ' created.';
	}
}
