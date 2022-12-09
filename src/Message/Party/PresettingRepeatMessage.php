<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class PresettingRepeatMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'New trades will repeat by default.';
	}
}
