<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class NonAggressionPactLastMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected function create(): string {
		return 'Your units are protected from attacks for this round.';
	}
}
