<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class LearnProgressMessage extends LearnNotMessage
{
	protected Result $result = Result::SUCCESS;

	protected int $experience;

	protected function create(): string {
		return 'Unit ' . $this->id . ' learns ' . $this->talent . ' with ' . $this->experience . ' experience.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->experience = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'experience') ?? parent::getTranslation($name);
	}
}
