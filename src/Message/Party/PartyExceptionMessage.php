<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class PartyExceptionMessage extends AbstractPartyMessage
{
	protected string $level = Message::ERROR;

	protected string $exception;

	protected function create(): string {
		return 'Error in orders: ' . $this->exception;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->exception = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'exception') {
			if (str_starts_with($this->exception, 'Unknown command')) {
				return str_replace('Unknown command', 'unbekannter Befehl', $this->exception);
			}
		}
		return parent::getTranslation($name);
	}
}
