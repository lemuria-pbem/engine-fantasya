<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class WoundshutMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::MAGIC;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is healing its wounds.';
	}
}
