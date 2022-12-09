<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class EnterPortSmuggleMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'Unit ' . $this->id . ' successfully smuggles luxuries from the vessel.';
	}
}
