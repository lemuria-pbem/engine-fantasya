<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class ColorOutOfSpacePoisonMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Event;

	protected Item $peasants;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->peasants . ' seem to have been poisoned and die.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->peasants = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'peasants') ?? parent::getTranslation($name);
	}
}
