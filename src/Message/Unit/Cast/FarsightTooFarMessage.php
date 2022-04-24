<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class FarsightTooFarMessage extends FarsightUnknownMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough Aura to cast Farsight on distant region ' . $this->region . '.';
	}
}
