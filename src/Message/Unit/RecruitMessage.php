<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class RecruitMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $size;

	protected function create(): string {
		return 'Unit ' . $this->id . ' recruits ' . $this->size . ' peasants';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
	}
}
