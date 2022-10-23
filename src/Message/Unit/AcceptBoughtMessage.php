<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class AcceptBoughtMessage extends AcceptOfferMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' bought ' . $this->quantity . ' from merchant ' . $this->unit . ' for ' . $this->payment . '.';
	}
}
