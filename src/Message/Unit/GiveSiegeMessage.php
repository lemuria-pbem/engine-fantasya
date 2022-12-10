<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class GiveSiegeMessage extends GiveFailedMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything to unit ' . $this->recipient . ' due to a siege.';
	}
}
