<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class EnterPortSmuggleMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'Unit ' . $this->id . ' successfully smuggles luxuries from the vessel.';
	}
}
