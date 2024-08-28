<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AuraPortalStabilizesMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'The effect of the aura portal stabilizes ';
	}
}
