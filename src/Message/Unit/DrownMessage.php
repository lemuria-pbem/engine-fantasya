<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class DrownMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected Id $ocean;

	protected function create(): string {
		return 'Unit ' . $this->id . ' drowns in ocean ' . $this->ocean . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->ocean = $message->get();
	}
}
