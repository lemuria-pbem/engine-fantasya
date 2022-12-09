<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class AcceptBoughtMessage extends AcceptOfferMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' buys ' . $this->quantity . ' from merchant ' . $this->unit . ' for ' . $this->payment . '.';
	}
}
