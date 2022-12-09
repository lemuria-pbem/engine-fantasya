<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class ApplyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Magic;

	protected Item $potion;

	protected function create(): string {
		return 'Unit ' . $this->id . ' applies ' . $this->potion . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->potion = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'potion') ?? parent::getTranslation($name);
	}
}
