<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class FollowMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected Id $leader;

	protected function create(): string {
		return 'Unit ' . $this->id . ' follows unit ' . $this->leader . ' on its way.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->leader = $message->get();
	}
}
