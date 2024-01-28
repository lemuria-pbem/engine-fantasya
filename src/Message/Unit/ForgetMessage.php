<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class ForgetMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Study;

	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' forgets all knowledge in ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->talent($name, 'talent') ?? parent::getTranslation($name);
	}
}
