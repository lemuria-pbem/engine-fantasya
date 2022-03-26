<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Section;

class EagleEyeMessage extends AbstractUnitMessage
{
	protected Section $section = Section::EVENT;

	protected int $perception;

	protected function create(): string {
		return 'Unit ' . $this->id . ' now has Perception level ' . $this->perception . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->perception = $message->getParameter();
	}
}
