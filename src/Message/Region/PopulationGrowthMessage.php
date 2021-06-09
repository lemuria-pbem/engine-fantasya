<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class PopulationGrowthMessage extends AbstractRegionMessage
{
	protected int $section = Section::ECONOMY;

	protected Item $peasants;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->peasants . ' are born.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->peasants = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'peasants') ?? parent::getTranslation($name);
	}
}
