<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class BreedingSuccessMessage extends AbstractConstructionMessage
{
	protected Section $section = Section::Production;

	protected Item $growth;

	protected function create(): string {
		return $this->growth . ' are born on this farm.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->growth = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'growth') ?? parent::getTranslation($name);
	}
}
