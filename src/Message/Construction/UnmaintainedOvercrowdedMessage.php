<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class UnmaintainedOvercrowdedMessage extends UnmaintainedMessage
{
	protected function create(): string {
		return 'Construction ' . $this->id . ' cannot be used as it is overcrowded.';
	}
}
