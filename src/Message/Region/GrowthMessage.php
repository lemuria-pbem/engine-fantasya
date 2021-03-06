<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class GrowthMessage extends AbstractRegionMessage
{
	protected Item $trees;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->trees . ' are growing.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trees = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'trees') ?? parent::getTranslation($name);
	}
}
