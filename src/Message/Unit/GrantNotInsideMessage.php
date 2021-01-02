<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class GrantNotInsideMessage extends GrantFromOutsideMessage
{
	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot grant command. Target unit ' . $this->target . ' is not inside.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = new Id($message->getParameter());
	}
}
