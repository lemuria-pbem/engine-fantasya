<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RetirementMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected function create(): string {
		return 'You have lost your last unit, and retire. This will be your last report. Farewell!';
	}
}
