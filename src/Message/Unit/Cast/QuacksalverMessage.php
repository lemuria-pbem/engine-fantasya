<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class QuacksalverMessage extends AbstractCastMessage
{
	protected Item $silver;

	protected function create(): string {
		return 'Unit ' . $this->id . ' earned ' . $this->silver . ' with Quacksalver.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->silver = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
