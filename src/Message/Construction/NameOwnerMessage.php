<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NameOwnerMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::FAILURE;

	protected Id $owner;

	protected function create(): string {
		return 'Unit ' . $this->owner . ' is not owner of construction ' . $this->id . ' and thus cannot rename it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->owner = $message->get();
	}
}
