<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class TakeUnsupportedMessage extends ReadUnsupportedMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::ERROR;

	protected Singleton $composition;

	protected Id $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot take ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->composition = $message->getSingleton();
		$this->unicum      = $message->get();
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
