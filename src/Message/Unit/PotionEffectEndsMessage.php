<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class PotionEffectEndsMessage extends PotionEffectContinuesMessage
{
	protected function create(): string {
		return 'The effect of ' . $this->potion . ' ends.';
	}
}
