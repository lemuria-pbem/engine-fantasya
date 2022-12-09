<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Item;

class FindWalletMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected Id $unit;

	protected Item $silver;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has found a lost wallet containing ' . $this->silver . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit  = $message->get();
		$this->silver = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
