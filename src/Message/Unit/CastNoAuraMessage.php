<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class CastNoAuraMessage extends CastExperienceMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough Aura left to cast ' . $this->spell . '.';
	}
}
