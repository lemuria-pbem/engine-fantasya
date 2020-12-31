<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class NameRegionMessage extends AbstractRegionMessage
{
	protected string $level = Message::SUCCESS;

	protected string $name;

	protected function create(): string {
		return 'Region ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
