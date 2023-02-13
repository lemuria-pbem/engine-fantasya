<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class PortFeeMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected Item $fee;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays the port fee ' . $this->fee . ' to the harbour master.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->fee = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'fee') ?? parent::getTranslation($name);
	}
}
