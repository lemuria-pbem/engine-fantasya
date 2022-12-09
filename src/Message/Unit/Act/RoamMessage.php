<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class RoamMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' roams to region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
