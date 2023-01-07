<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class GiveFailedMessage extends GiveNotFoundMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything to unit ' . $this->recipient . '. The recipient refuses to accept gifts from us.';
	}
}
