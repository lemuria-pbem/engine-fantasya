<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class RoamHereMessage extends RoamMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' roams in region ' . $this->region . '.';
	}
}
