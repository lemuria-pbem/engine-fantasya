<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class UpkeepNothingMessage extends AbstractUnitMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot pay the upkeep for construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
