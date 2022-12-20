<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class RetirementMessage extends AbstractPartyMessage
{
	protected string $level = Message::EVENT;

	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'You have lost your last unit, and retire. This will be your last report. Farewell!';
	}
}
