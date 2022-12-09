<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class PresettingLootMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::BATTLE;

	protected function create(): string {
		return 'New recruits will gather loot by default.';
	}
}
