<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class PickPocketNothingMessage extends PickPocketLeaveMessage
{
	protected Id $enemy;

	protected function create(): string {
		return 'Unit ' . $this->id . ' did not find any silver in the pockets of unit ' . $this->enemy . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->enemy = $message->get();
	}
}
