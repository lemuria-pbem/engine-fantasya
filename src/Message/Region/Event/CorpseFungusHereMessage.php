<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;

class CorpseFungusHereMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'The Corpse Fungus is infesting the dead in region ' . $this->id . ' and devours their flesh.';
	}
}
