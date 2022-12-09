<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class NotImplementedMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Error;

	protected Section $section = Section::Error;

	protected string $command;

	protected function create(): string {
		return 'The command "' . $this->command . '" is not implemented in Lemuria.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->command = $message->getParameter();
	}
}
