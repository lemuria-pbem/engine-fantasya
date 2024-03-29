<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class BreedingGrowthMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected Item $growth;

	protected function create(): string {
		return 'Unit ' . $this->id . ' breeds ' . $this->growth . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->growth = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'growth') ?? parent::getTranslation($name);
	}
}
