<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Item;

class BuyMessage extends AbstractUnitMessage
{
	public final const string PAYMENT = 'payment';

	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected Id $region;

	protected Item $goods;

	protected Item $payment;

	protected function create(): string {
		return 'Unit ' . $this->id . ' buys ' . $this->goods . ' from the peasants in region ' . $this->region . ' for ' . $this->payment . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region  = $message->get();
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
