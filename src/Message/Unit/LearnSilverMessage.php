<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class LearnSilverMessage extends LearnNotMessage
{
	protected string $level = Message::SUCCESS;

	protected int $silver;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->silver . ' silver to learn ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->silver = $message->getParameter();
	}
}
