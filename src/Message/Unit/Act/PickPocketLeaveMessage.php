<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class PickPocketLeaveMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will move on next week.';
	}
}
