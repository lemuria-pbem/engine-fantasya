<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class UnmaintainedMessage extends AbstractConstructionMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::ECONOMY;

	protected function create(): string {
		return 'Construction ' . $this->id . ' cannot be used as it was not maintained.';
	}
}
