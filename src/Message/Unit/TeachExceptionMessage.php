<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class TeachExceptionMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	private string $error;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach. ' . $this->error;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->error = $message->getParameter();
	}
}
