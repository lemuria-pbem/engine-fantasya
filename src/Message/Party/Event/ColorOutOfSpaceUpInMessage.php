<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Event;

class ColorOutOfSpaceUpInMessage extends ColorOutOfSpaceWellInMessage
{
	protected function create(): string {
		return 'Shortly after the last poisoned peasant died in ' . $this->region . ' , the strange pale violet ' .
			   'light surfaces again: Countless big and small shining spheres slowly rise where once the pillar of ' .
			   'light plunged, ever faster rising up into heaven. Soon they have vanished out of view.';
	}
}
