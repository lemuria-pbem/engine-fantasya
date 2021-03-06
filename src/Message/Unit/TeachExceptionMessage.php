<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class TeachExceptionMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected string $error;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach. ' . $this->error;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->error = $message->getParameter();
	}
}
