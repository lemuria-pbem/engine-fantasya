<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class LearnSpellAlreadyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Magic;

	protected Singleton $spell;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has already learned the spell ' . $this->spell . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->spell = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->spell($name, 'spell') ?? parent::getTranslation($name);
	}
}
