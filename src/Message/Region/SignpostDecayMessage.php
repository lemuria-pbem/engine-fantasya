<?php
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class SignpostDecayMessage extends AbstractRegionMessage
{
	protected string $level = Message::EVENT;

	protected string $name;

	protected function create(): string {
		return "The signpost '" . $this->name . "' has fallen into ruin.";
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
