<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Model\Fantasya\Quantity;

class DistributorFleetMessage extends AbstractUnitMessage
{
	protected Section $section = Section::Economy;

	protected Quantity $quantity;

	protected int $capacity;

	public function create(): string {
		return 'Realm fleet has remaining capacity of ' . $this->capacity . ' (' . $this->quantity . ').';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->capacity = $message->getParameter();
		$this->quantity = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'capacity') {
			return $this->number($name, 'capacity');
		}
		return $this->item($name, 'quantity', casus: Casus::Nominative) ?? parent::getTranslation($name);
	}
}
