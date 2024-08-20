<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class FiefNotFoundMessage extends FiefNoneMessage
{
	protected string $receiver;

	protected function create(): string {
		return 'Unit ' . $this->receiver . ' could not be found.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->receiver = $message->getParameter();
	}
}
