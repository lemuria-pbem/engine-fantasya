<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class RoutePauseMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' paused the route this week.';
	}
}
