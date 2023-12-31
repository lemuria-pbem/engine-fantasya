<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class GreenhousingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected Item $grow;

	protected function create(): string {
		return 'Unit ' . $this->id . ' succeeds in growing ' . $this->grow . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->grow = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'grow') ?? parent::getTranslation($name);
	}
}
