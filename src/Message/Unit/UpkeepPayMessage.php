<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Item;

class UpkeepPayMessage extends AbstractUnitMessage
{
	protected Section $section = Section::ECONOMY;

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
