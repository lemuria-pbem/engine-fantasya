<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class DescribeMonumentOnceMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::ERROR;

	protected function create(): string {
		return 'The description of a monument cannot be changed.';
	}
}
