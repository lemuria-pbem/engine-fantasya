<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TempMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'New unit ' . $this->id . ' created.';
	}
}
