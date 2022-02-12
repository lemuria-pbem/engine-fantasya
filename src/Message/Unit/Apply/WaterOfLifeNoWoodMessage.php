<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class WaterOfLifeNoWoodMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::MAGIC;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no wood to grow saplings with Water of Life.';
	}
}
