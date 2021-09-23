<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class FighterSavedMessage extends AbstractFighterMessage
{
	public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is saved from a deadly strike by a healing potion.';
	}
}
