<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TravelUnitMessage extends AbstractRegionMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected string $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has travelled through region ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->getParameter();
	}
}
