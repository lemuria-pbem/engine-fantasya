<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class EnterDeniedMessage extends EnterAlreadyMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot enter the construction ' . $this->construction . ' without permission from owner.';
	}
}
