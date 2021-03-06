<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class FaunaGriffineggMessage extends AbstractRegionMessage
{
	protected Item $egg;

	protected function create(): string {
		return 'In region ' . $this->id . ' the griffins lay ' . $this->egg . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->egg = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'egg') ?? parent::getTranslation($name);
	}
}
