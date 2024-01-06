<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\CivilCommotionEffect;
use Lemuria\Engine\Fantasya\Effect\RestInPeaceEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\CivilCommotionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RaiseTheDeadPeaceMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;

final class RaiseTheDead extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit = $this->cast->Unit();
			$unit->Aura()->consume($aura);
			$region = $unit->Region();
			if ($this->isRestInPeaceActiveIn($region)) {
				$this->message(RaiseTheDeadPeaceMessage::class, $unit)->e($region);
				return;
			}

			//TODO create zombies from starved peasants with control effect
			//TODO add effect for later combat
			//TODO control effect consumes aura if zombies are not dismissed
			//TODO dismiss zombies if summoner runs away
		}
	}

	private function isRestInPeaceActiveIn(Region $region): bool {
		$effect = new RestInPeaceEffect(State::getInstance());
		return (bool)Lemuria::Score()->find($effect->setRegion($region));
	}
}
