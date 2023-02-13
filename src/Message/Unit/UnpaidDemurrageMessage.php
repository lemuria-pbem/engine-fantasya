<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class UnpaidDemurrageMessage extends TravelSimulationMessage
{
	protected Item $fee;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot pay outstanding demurrage ' . $this->fee . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->fee = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'fee') ?? parent::getTranslation($name);
	}
}
