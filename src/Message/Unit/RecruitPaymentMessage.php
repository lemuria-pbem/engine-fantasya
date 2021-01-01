<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use function Lemuria\getClass;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Model\Lemuria\Quantity;

class RecruitPaymentMessage extends RecruitMessage
{
	protected string $level = Message::DEBUG;

	protected Quantity $cost;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->cost . ' for ' . $this->size . ' recruits.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->cost = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'cost') {
			$commodity = getClass($this->cost->Commodity());
			$index     = $this->cost->Count() > 1 ? 1 : 0;
			$cost = $this->translateKey('resource.' . $commodity, $index);
			if ($cost) {
				return $cost;
			}
		}
		return parent::getTranslation($name);
	}
}
