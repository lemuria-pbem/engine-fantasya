<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

class ColorOutOfSpaceDownMessage extends ColorOutOfSpaceSummonMessage
{
	protected function create(): string {
		return 'In this last night of low voices something special is happening: From heaven high above in ' .
			   $this->direction . ' suddenly a mighty pillar of strange pale violet light rushes down. Some peasants ' .
			   'nearby report that it pierces the surface and sinks down into the water. For some time they watch ' .
			   'a glow in the deep until it is vanished.';
	}
}
