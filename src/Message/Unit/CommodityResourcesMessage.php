<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class CommodityResourcesMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Singleton $artifact;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to create ' . $this->artifact . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->artifact = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'artifact', 1) ?? parent::getTranslation($name);
	}
}
