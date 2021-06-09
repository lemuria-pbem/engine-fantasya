<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class ConstructionMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::PRODUCTION;

	protected Singleton $building;

	protected function create(): string {
		return 'Unit ' . $this->id . ' creates a new ' . $this->building . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->building($name, 'building') ?? parent::getTranslation($name);
	}
}
