<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TeachSelfMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::STUDY;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach itself.';
	}
}
