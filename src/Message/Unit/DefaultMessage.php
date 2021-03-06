<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class DefaultMessage extends AbstractUnitMessage
{
	protected string $command;

	protected function create(): string {
		return 'Unit ' . $this->id . ' adds default: ' . $this->command;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->command = $message->getParameter();
	}
}
