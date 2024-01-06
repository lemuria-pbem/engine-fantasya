<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\DetectMetalsEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class DetectMetals extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit = $this->cast->Unit();
			$unit->Aura()->consume($aura);
			$region   = $unit->Region();
			$effect   = new DetectMetalsEffect(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setParty($unit->Party()));
			if ($existing) {
				$effect = $existing;
			} else {
				Lemuria::Score()->add($effect);
			}
			$effect->Regions()->add($region);

		}
	}
}
