<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class FollowerMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Movement;

	protected Id $follower;

	protected function create(): string {
		return 'From now on unit ' . $this->follower . ' follows us everywhere we go.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->follower = $message->get();
	}
}
