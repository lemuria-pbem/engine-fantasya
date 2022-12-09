<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Quantity;

class AcceptOfferMessage extends AbstractUnitMessage
{
	public final const PAYMENT = 'payment';

	public final const UNIT = 'unit';

	protected Result $result = Result::EVENT;

	protected Section $section = Section::ECONOMY;

	protected Id $trade;

	protected Id $unit;

	protected Quantity $quantity;

	protected Quantity $payment;

	protected function create(): string {
		return 'Customer ' . $this->unit . ' accepts offer ' . $this->trade . ' and buys ' . $this->quantity . ' for ' . $this->payment . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade    = $message->get();
		$this->unit     = $message->get(self::UNIT);
		$this->quantity = $message->getQuantity();
		$this->payment  = $message->getQuantity(self::PAYMENT);
	}

	protected function getTranslation(string $name): string {
		if ($name === self::PAYMENT) {
			return $this->item($name, self::PAYMENT);
		}
		return $this->item($name, 'quantity') ?? parent::getTranslation($name);
	}
}
