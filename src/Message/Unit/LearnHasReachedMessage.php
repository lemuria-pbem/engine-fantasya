<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class LearnHasReachedMessage extends LearnNotMessage
{
	protected Result $result = Result::Debug;

	protected int $experience;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has reached the desired level ' . $this->experience . ' in learning ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->experience = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'experience') ?? parent::getTranslation($name);
	}
}
