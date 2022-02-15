<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Item;

class UnicumMaterialMessage extends UnicumNoMaterialMessage
{
	protected string $level = Message::SUCCESS;

	protected Item $material;

	protected function create(): string {
		return 'Unit ' . $this->id . ' spends ' . $this->material . ' to create a ' . $this->composition . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->material = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'material') ?? parent::getTranslation($name);
	}
}
