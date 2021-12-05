<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class GriffineggAttackedMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected int $section = Section::BATTLE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is attacked by the griffins after attempting to steal eggs.';
	}
}
