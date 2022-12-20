<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class RetirementPartyMessage extends RetirementMessage
{
	protected string $level = Message::EVENT;

	protected Id $party;

	protected string $name;

	protected function create(): string {
		return 'It seems that the party ' . $this->name . ' [' .  $this->party . '] has left this world.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
		$this->name  = $message->getParameter();
	}
}
