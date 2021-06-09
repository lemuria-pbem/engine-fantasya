<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class FightUnguardMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::BATTLE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not guard the region anymore.';
	}
}
