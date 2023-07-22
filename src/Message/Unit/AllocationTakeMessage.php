<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Model\Fantasya\Quantity;

class AllocationTakeMessage extends AbstractUnitMessage
{
	protected Section $section = Section::Production;

	protected Quantity $item;

	protected function create(): string {
		return 'Unit ' . $this->id . ' takes ' . $this->item . ' from the pool.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->item = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'item', casus: Casus::Adjective) ?? parent::getTranslation($name);
	}
}
