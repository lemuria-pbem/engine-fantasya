<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class ExploreNothingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected Reliability $reliability = Reliability::Unreliable;

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has found no herbs in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
