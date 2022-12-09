<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class NamePartyMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected string $name;

	protected function create(): string {
		return 'Party ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
