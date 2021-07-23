<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class AttackFromMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is attacked by party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
