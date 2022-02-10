<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class UnicumNoMaterialMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Singleton $composition;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to create a ' . $this->composition . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'composition') ?? parent::getTranslation($name);
	}
}