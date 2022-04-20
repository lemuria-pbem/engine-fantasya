<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\FarsightEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightTooFarMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightUnknownMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;

final class Farsight extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit   = $this->cast->Unit();
			$region = $this->cast->Region();
			if (!$region) {
				$region = $unit->Region();
			}
			$distance = Lemuria::World()->getDistance($unit->Region(), $region);

			$party     = $unit->Party();
			$chronicle = $party->Chronicle();
			if (!$chronicle->has($region->Id())) {
				$this->message(FarsightUnknownMessage::class, $unit)->e($region);
				return;
			}

			$aura += $distance;
			if ($unit->Aura()->Aura() >= $aura) {
				$unit->Aura()->consume($aura);
				$chronicle->add($region);
				$this->addEffect($region, $party);
				$this->message(FarsightMessage::class, $unit)->e($region);
			} else {
				$this->message(FarsightTooFarMessage::class, $unit)->e($region);
			}
		}
	}

	private function addEffect(Region $region, Party $party): void {
		$effect   = new FarsightEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		if (!$existing) {
			Lemuria::Score()->add($effect);
		} else {
			$effect = $existing;
		}
		$effect->Parties()->add($party);
	}
}
