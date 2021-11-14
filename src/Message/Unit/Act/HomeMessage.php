<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class HomeMessage extends RoamMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' roams to region ' . $this->region . ' in search for a home.';
	}
}
