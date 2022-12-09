<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class PresettingHideMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Movement;

	protected function create(): string {
		return 'New units will hide by default.';
	}
}
