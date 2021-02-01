<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class SmashNotConstructionMessageOwnerMessage extends SmashNotInConstructionMessage
{
	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be owner of the construction ' . $this->construction . ' to destroy it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
