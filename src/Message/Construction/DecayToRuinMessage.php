<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class DecayToRuinMessage extends DecayMessage
{
	protected function create(): string {
		return 'The ravages of time have ruined the former ' . $this->building . ' ' . $this->id . '.';
	}
}
