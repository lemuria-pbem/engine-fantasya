<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;
use Lemuria\Item;

class UpkeepPayMessage extends AbstractUnitMessage
{
	protected Id $construction;

	protected Item $upkeep;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->upkeep . ' upkeep for construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
		$this->upkeep       = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'upkeep') ?? parent::getTranslation($name);
	}
}
