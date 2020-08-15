<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class FightUnguardMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' does not guard the region anymore.';
	}
}
