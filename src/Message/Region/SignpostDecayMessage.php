<?php
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class SignpostDecayMessage extends AbstractRegionMessage
{
	protected Result $result = Result::EVENT;

	protected string $name;

	protected function create(): string {
		return "The signpost '" . $this->name . "' has fallen into ruin.";
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
