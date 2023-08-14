<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class SupportNothingMessage extends SupportHungerMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot pay any support.';
	}
}
