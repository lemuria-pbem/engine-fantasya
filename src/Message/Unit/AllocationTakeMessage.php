<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Model\Fantasya\Quantity;

class AllocationTakeMessage extends AbstractUnitMessage
{
	protected Quantity $item;

	protected function create(): string {
		return 'Unit ' . $this->id . ' takes ' . $this->item . ' from the pool.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->item = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'item') {
			$commodity = getClass($this->item->Commodity());
			$index     = $this->item->Count() > 1 ? 1 : 0;
			$cost = $this->translateKey('resource.' . $commodity, $index);
			if ($cost) {
				return $this->item->Count() . ' ' . $cost;
			}
		}
		return parent::getTranslation($name);
	}
}
