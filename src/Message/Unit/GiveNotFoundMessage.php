<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class GiveNotFoundMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Id $recipient;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything, target unit ' . $this->recipient . ' is not here.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->recipient = $message->get();
	}
}
