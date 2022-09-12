<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class VisitNoMarketMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::MAIL;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot visit any merchant unit - there is no market here.';
	}
}
