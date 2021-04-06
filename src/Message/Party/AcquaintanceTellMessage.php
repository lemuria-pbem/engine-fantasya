<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AcquaintanceTellMessage extends AcquaintanceMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'We have initiated diplomatic relations to ' . $this->party . ' and told unit ' . $this->unit . ' about our people.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
