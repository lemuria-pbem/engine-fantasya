<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class FollowingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Movement;

	protected Id $leader;

	protected function create(): string {
		return 'From now on we will follow unit ' . $this->leader . ' wherever it goes.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->leader = $message->get();
	}
}
