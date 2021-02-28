<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Item;

class PopulationGrowthMessage extends AbstractRegionMessage
{
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
