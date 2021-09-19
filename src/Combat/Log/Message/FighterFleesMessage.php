<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class FighterFleesMessage extends AbstractFighterMessage
{
	public function getDebug(): string {
		return 'Fighter ' . $this->fighter . ' is wounded and flees from battle.';
	}
}
