<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class TravelIntoMonsterMessage extends TravelRegionMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was ambushed in region ' . $this->region . ' by monsters and stopped.';
	}
}
