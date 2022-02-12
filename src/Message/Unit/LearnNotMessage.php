<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class LearnNotMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::STUDY;

	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot pay any silver to learn ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->talent($name, 'talent') ?? parent::getTranslation($name);
	}
}
