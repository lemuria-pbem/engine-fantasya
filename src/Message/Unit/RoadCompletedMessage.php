<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Item;

class RoadCompletedMessage extends RoadAlreadyCompletedMessage
{
	protected Result $result = Result::SUCCESS;

	protected Item $stones;

	protected function create(): string {
		return 'Unit ' . $this->id . ' finishes the road to ' . $this->direction . ' in region ' . $this->region . ' with ' . $this->stones . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->stones = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'stones') ?? parent::getTranslation($name);
	}
}
