<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class BuyMessage extends AbstractUnitMessage
{
	public const PAYMENT = 'payment';

	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::PRODUCTION;

	protected Item $goods;

	protected Item $payment;

	protected function create(): string {
		return 'Unit ' . $this->id . ' buys ' . $this->goods . ' from the peasants for ' . $this->payment . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->goods   = $message->getQuantity();
		$this->payment = $message->getQuantity(self::PAYMENT);
	}

	protected function getTranslation(string $name): string {
		if ($name === 'goods') {
			return $this->item($name, 'goods');
		}
		if ($name === self::PAYMENT) {
			return $this->item($name, self::PAYMENT);
		}
		return parent::getTranslation($name);
	}
}
