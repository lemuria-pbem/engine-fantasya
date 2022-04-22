<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class NameMonumentOnceMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::ERROR;

	protected function create(): string {
		return 'A monument cannot be renamed.';
	}
}
