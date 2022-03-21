<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class PresettingHideMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'New units will hide by default.';
	}
}
