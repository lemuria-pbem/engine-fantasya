<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class PartyExceptionMessage extends AbstractPartyMessage
{
	protected string $level = Message::ERROR;

	protected int $section = Section::ERROR;

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
				return str_replace('Unknown command', 'Unbekannter Befehl', $this->exception);
			}
			if (preg_match('/Entity ([0-9a-z]+) is not registered in this catalog./', $this->exception, $matches) === 1) {
				return 'Die Einheit ' . $matches[1] . ' gehört nicht zu uns';
			}
			if (str_starts_with($this->exception, 'Skipping command')) {
				return substr($this->exception, 17, strlen($this->exception) - 18) . ' übersprungen';
			}
		}
		return parent::getTranslation($name);
	}
}
