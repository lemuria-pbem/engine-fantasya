<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\Reliability;

class UnmaintainedOvercrowdedMessage extends UnmaintainedMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Construction ' . $this->id . ' cannot be used as it is overcrowded.';
	}
}
