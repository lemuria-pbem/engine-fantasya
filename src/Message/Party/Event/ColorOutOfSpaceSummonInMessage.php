<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Event;

class ColorOutOfSpaceSummonInMessage extends ColorOutOfSpaceDownInMessage
{
	protected function create(): string {
		return 'In ' . $this->region . ' in the darkest hour in the nights vague low voices are in the air, and each ' .
			   'time a gust of wind comes up from ' . $this->direction . ' that soon gets calm again. Along the way ' .
			   'some isolated sheet lightning can be seen behind the clouds.';
	}
}
