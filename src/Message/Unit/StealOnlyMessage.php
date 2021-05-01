<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class StealOnlyMessage extends StealOwnUnitMessage
{
	protected Item $pickings;

	protected function create(): string {
		return 'Unit ' . $this->id . ' could only steal ' . $this->pickings . ' from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->pickings = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'pickings') ?? parent::getTranslation($name);
	}
}
