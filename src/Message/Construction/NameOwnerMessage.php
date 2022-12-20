<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NameOwnerMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Failure;

	protected Id $owner;

	protected function create(): string {
		return 'Unit ' . $this->owner . ' is not a member of the owning party of construction ' . $this->id . ' and thus cannot rename it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->owner = $message->get();
	}
}
