<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class LoseToUnitMessage extends LoseMessage
{
	protected Id $from;

	protected function create(): string {
		return 'Unit ' . $this->id . ' inherits ' . $this->quantity . ' from unit' . $this->from . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->from = $message->get();
	}
}
