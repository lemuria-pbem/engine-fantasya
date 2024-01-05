<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\CivilCommotionEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\CivilCommotionMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class RaiseTheDead extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit = $this->cast->Unit();
			$unit->Aura()->consume($aura);
			$region   = $unit->Region();
			$effect   = new CivilCommotionEffect(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setRegion($region));
			if (!$existing) {
				Lemuria::Score()->add($effect);
				$this->message(CivilCommotionMessage::class, $region);
			}
		}
	}
}
