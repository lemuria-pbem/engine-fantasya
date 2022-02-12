<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class ApplyAlreadyMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::MAGIC;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot apply another potion.';
	}
}
