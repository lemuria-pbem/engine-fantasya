<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class FaunaGrowthMessage extends AbstractRegionMessage
{
	protected Section $section = Section::ECONOMY;

	protected Item $animals;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->animals . ' are born.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->animals = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'animals') ?? parent::getTranslation($name);
	}
}
