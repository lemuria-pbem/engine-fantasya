<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class AttackSelfMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::BATTLE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will not fight itself.';
	}
}
