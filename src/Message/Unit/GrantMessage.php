<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class GrantMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' grants command to unit ' . $this->target . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = $message->get();
	}
}
