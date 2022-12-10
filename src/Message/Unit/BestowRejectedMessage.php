<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class BestowRejectedMessage extends GiveFailedMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Unit ' . $this->recipient . ' wanted to give ' . $this->id . ' an unicum.';
	}
}
