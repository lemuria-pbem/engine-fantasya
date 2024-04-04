<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;

class ColorOutOfSpaceWellMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'In this last night of low voices something special is happening: From heaven high above suddenly a ' .
			   'mighty pillar of strange pale violet light rushes down. Some peasants nearby report that this ' .
			   'outlandish light flows above the ground like a liquid until it reaches a fountain, trickling down.' .
			   'For some time they watch a glow in the deep until it is vanished.';
	}
}
