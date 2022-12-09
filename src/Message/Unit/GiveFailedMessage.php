<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class GiveFailedMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Id $recipient;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything to unit ' . $this->recipient . '. The recipient refuses to accept gifts from us.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->recipient = $message->get();
	}
}
