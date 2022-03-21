<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class PresettingLootMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::BATTLE;

	protected function create(): string {
		return 'New recruits will gather loot by default.';
	}
}
