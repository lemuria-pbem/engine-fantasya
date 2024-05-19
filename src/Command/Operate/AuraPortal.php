<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Message\Unit\Operate\AuraPortalMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\AuraPortalNotMessage;

final class AuraPortal extends AbstractOperate
{
	private const float BURN = 0.1;

	public function apply(): void {
		$unicum      = $this->operator->Unicum();
		$composition = $unicum->Composition();
		$aura        = $this->unit->Aura();
		if ($aura) {
			$aura->setAura($aura->Maximum());
			$this->message(AuraPortalMessage::class, $this->unit)->e($unicum)->s($composition);
		} else {
			$this->unit->setHealth($this->unit->Health() - self::BURN);
			$this->message(AuraPortalNotMessage::class, $this->unit)->e($unicum)->s($composition);
		}
	}
}
