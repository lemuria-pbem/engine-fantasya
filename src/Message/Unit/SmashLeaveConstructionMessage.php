<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class SmashLeaveConstructionMessage extends SmashNotConstructionMessageOwnerMessage
{
	protected string $level = LemuriaMessage::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has left the construction ' . $this->construction . ' before it is destroyed.';
	}
}
