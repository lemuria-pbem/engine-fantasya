<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Construction;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class LeaveNewOwnerMessage extends AbstractConstructionMessage
{
	protected Id $owner;

	protected function create(): string {
		return 'Construction ' . $this->id . ' has new owner ' . $this->owner . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->owner = $message->get();
	}
}
