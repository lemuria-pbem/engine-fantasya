<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Model\Fantasya\Quantity;

class FeeQuantityMessage extends FeeNoneMessage
{
	protected Quantity $fee;

	protected function create(): string {
		return 'The fee for the ' . $this->building . ' ' . $this->id . ' has been set to ' . $this->fee . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->fee = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'fee') ?? parent::getTranslation($name);
	}
}
