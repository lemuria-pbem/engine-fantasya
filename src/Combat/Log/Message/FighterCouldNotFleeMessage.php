<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class FighterCouldNotFleeMessage extends AbstractFighterMessage
{
	public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is wounded, but could not flee from battle.';
	}
}
