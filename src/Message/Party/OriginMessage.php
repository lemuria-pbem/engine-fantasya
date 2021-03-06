<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Model\Fantasya\Region;

class OriginMessage extends OriginNotVisitedMessage
{
	protected string $level = Message::SUCCESS;

	protected string $name;

	protected function create(): string {
		return 'Map origin has been set to region ' . $this->name . ' [' . $this->region . '].';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = Region::get($this->region)->Name();
	}
}
