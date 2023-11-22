<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class PopulationFeedMessage extends PopulationGrowthMessage
{
	public final const string SILVER = 'silver';

	protected Item $silver;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->peasants . ' consume ' . $this->silver . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->silver = $message->getQuantity(self::SILVER);
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
